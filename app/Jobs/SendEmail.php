<?php

namespace App\Jobs;

use App\Mail\VanillaMail;
use App\Utilities\Contracts\ElasticsearchHelperInterface;
use App\Utilities\Contracts\RedisHelperInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string Uuid
     */
    protected string $uuid;

    /**
     * @var string Email address
     */
    protected string $emailAddress;

    /**
     * @var string Subject
     */
    protected string $subject;

    /**
     * @var string Body
     */
    protected string $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $uuid, string $emailAddress, string $subject, string $body)
    {
        $this->uuid = $uuid;
        $this->emailAddress = $emailAddress;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mail = new VanillaMail($this->body);
        $mail->subject($this->subject);

        Mail::to($this->emailAddress)->send($mail);

        /** @var ElasticsearchHelperInterface $elasticsearchHelper */
        $elasticsearchHelper = app()->make(ElasticsearchHelperInterface::class);
        $elasticsearchHelper->storeEmail($this->uuid, $this->body, $this->subject, $this->emailAddress);

        /** @var RedisHelperInterface $redisHelper */
        $redisHelper = app()->make(RedisHelperInterface::class);
        $redisHelper->storeRecentMessage($this->uuid, $this->subject, $this->emailAddress);
    }
}
