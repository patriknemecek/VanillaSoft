<?php

namespace Tests\Feature\Controllers;

use App\Jobs\SendEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserSendEndpointTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Test send endpoint without api_token parameter
     *
     * @return void
     */
    public function test_forbidden_without_token()
    {
        $url = vsprintf('api/%d/send', [
            $this->user->id,
        ]);

        $response = $this->postJson($url, [
            'emails' => [
                [
                    'email_address' => $this->faker->email(),
                    'subject' => $this->faker->text(),
                    'body' => $this->faker->realText(),
                ]
            ]
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test send endpoint with wrong api_token parameter
     *
     * @return void
     */
    public function test_forbidden_with_wrong_token()
    {
        $url = vsprintf('api/%d/send?api_token=%s', [
            $this->user->id,
            Str::random(64)
        ]);

        $response = $this->postJson($url, [
            'emails' => [
                [
                    'email_address' => $this->faker->email(),
                    'subject' => $this->faker->text(),
                    'body' => $this->faker->realText(),
                ]
            ]
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test send endpoint with wrong user id parameter
     *
     * @return void
     */
    public function test_forbidden_with_wrong_user_id()
    {
        $accessToken = $this->user->createToken('api_token')->accessToken;

        $url = vsprintf('api/%d/send?api_token=%s', [
            $this->faker->numberBetween(100, 1000),
            $accessToken->token
        ]);

        $response = $this->postJson($url, [
            'emails' => [
                [
                    'email_address' => $this->faker->email(),
                    'subject' => $this->faker->text(),
                    'body' => $this->faker->realText(),
                ]
            ]
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test dispatch send email jobs
     *
     * @return void
     */
    public function test_dispatch_send_email_jobs()
    {
        Queue::fake();

        $accessToken = $this->user->createToken('api_token')->accessToken;

        $url = vsprintf('api/%d/send?api_token=%s', [
            $this->user->id,
            $accessToken->token
        ]);

        $response = $this->postJson($url, [
            'emails' => [
                [
                    'email_address' => $this->faker->email(),
                    'subject' => $this->faker->text(),
                    'body' => $this->faker->realText(),
                ],
                [
                    'email_address' => $this->faker->email(),
                    'subject' => $this->faker->text(),
                    'body' => $this->faker->realText(),
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'status' => 'ok'
        ]);

        Queue::assertPushed(SendEmail::class, 2);
    }


    /**
     * Test various case of invalid input
     *
     * @dataProvider getInvalidInputData
     * @return void
     */
    public function test_invalid_input_in_send_email($data)
    {
        Queue::fake();

        $accessToken = $this->user->createToken('api_token')->accessToken;

        $url = vsprintf('api/%d/send?api_token=%s', [
            $this->user->id,
            $accessToken->token
        ]);

        $response = $this->postJson($url, [
            'emails' => $data['emails']
        ]);

        $response->assertStatus(422);
        $response->assertExactJson($data['response']);

        Queue::assertPushed(SendEmail::class, 0);
    }

    protected function getInvalidInputData()
    {
        $this->setUpFaker();

        return [
            // NULL DATA
            [
                [
                    'emails' => [],
                    'response' => [
                        'errors' => [
                            'emails' => [
                                'The emails field is required.'
                            ]
                        ],
                        'message' => 'The emails field is required.'
                    ]
                ],
            ],
            // NULL EMAIL DATA
            [
                [
                    'emails' => [
                        [
                            'email_address' => null,
                            'subject' => null,
                            'body' => null,
                        ]
                    ],
                    'response' => [
                        'errors' => [
                            'emails' => [
                                [
                                    'body' => [
                                        'The emails.0.body field is required.'
                                    ],
                                    'email_address' => [
                                        'The emails.0.email_address field is required.'
                                    ],
                                    'subject' => [
                                        'The emails.0.subject field is required.'
                                    ]
                                ]
                            ]
                        ],
                        'message' => 'The emails.0.email_address field is required. (and 2 more errors)'
                    ]
                ],
            ],
            // WRONG EMAIL
            [
                [
                    'emails' => [
                        [
                            'email_address' => $this->faker->text(),
                            'subject' => $this->faker->text(),
                            'body' => $this->faker->realText(),
                        ]
                    ],
                    'response' => [
                        'errors' => [
                            'emails' => [
                                [
                                    'email_address' => [
                                        'The emails.0.email_address must be a valid email address.'
                                    ]
                                ]
                            ]
                        ],
                        'message' => 'The emails.0.email_address must be a valid email address.'
                    ]
                ],

            ]
        ];
    }
}
