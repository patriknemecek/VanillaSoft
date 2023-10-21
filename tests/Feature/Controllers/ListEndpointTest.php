<?php

namespace Tests\Feature\Controllers;

use App\Jobs\SendEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use MailerLite\LaravelElasticsearch\Facade as Elasticsearch;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ListEndpointTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * Test list emails
     *
     * @return void
     */
    public function test_list_emails()
    {
        Mail::fake();
        Queue::fake();

        Artisan::call('laravel-elasticsearch:utils:index-delete', [
            'index-name' => 'emails'
        ]);

        $emails = $this->generateEmails();
        foreach ($emails as $emailData) {
            $job = new SendEmail($emailData['uuid'], $emailData['email_address'], $emailData['subject'], $emailData['body']);
            $job->handle();
        }

        Elasticsearch::indices()->refresh();

        $response = $this->getJson('api/list');

        $response->assertStatus(200);
        $response->assertExactJson([
            'data' => $emails
        ]);
    }

    protected function generateEmails($count = 50)
    {
        $emails = [];

        for ($i = 0; $i < $count; $i++) {
            $emails[] = [
                'uuid' => Uuid::uuid4()->toString(),
                'email_address' => $this->faker->email(),
                'subject' => $this->faker->text(),
                'body' => $this->faker->realText(),
            ];
        }

        return $emails;
    }
}
