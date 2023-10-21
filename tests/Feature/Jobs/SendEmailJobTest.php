<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendEmail;
use App\Mail\VanillaMail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use MailerLite\LaravelElasticsearch\Facade as Elasticsearch;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class SendEmailJobTest extends TestCase
{
    use WithFaker;

    /**
     * Test handle SendEmail job
     *
     * @return void
     */
    public function test_handle_job()
    {
        Mail::fake();

        Artisan::call('laravel-elasticsearch:utils:index-delete', [
            'index-name' => 'emails'
        ]);

        $uuid = Uuid::uuid4();
        $emailAddress = $this->faker->email();
        $subject = $this->faker->text();
        $body = $this->faker->realText();

        $job = new SendEmail($uuid, $emailAddress, $subject, $body);
        $job->handle();

        // Mail send
        Mail::assertSent(VanillaMail::class, 1);

        // Elasticsearch service
        Elasticsearch::indices()->refresh();

        $elasticResult = Elasticsearch::search(['index' => 'emails']);
        $hits = collect($elasticResult['hits']['hits']);

        $this->assertEquals(1, $hits->count());

        $hit = $hits->first();

        $this->assertEquals($uuid, $hit['_id']);
        $this->assertEquals($emailAddress, $hit['_source']['email_address']);
        $this->assertEquals($subject, $hit['_source']['subject']);
        $this->assertEquals($body, $hit['_source']['body']);

        // Redis service
        $cachedValue = Cache::get(vsprintf('messages.%s', [
            $uuid
        ]));

        $this->assertEquals([
            'email_address' => $emailAddress,
            'subject' => $subject,
        ], $cachedValue);
    }
}
