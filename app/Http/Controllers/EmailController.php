<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailSendRequest;
use App\Http\Resources\EmailResource;
use App\Jobs\SendEmail;
use App\Utilities\Contracts\ElasticsearchHelperInterface;
use Ramsey\Uuid\Uuid;

class EmailController extends Controller
{
    public function send(EmailSendRequest $request)
    {
        $data = $request->validated();

        foreach ($data['emails'] as $emailData) {
            SendEmail::dispatch(Uuid::uuid4(), $emailData['email_address'], $emailData['subject'], $emailData['body']);
        }

        return [
            'status' => 'ok'
        ];
    }

    public function list(ElasticsearchHelperInterface $elasticsearch)
    {
        $emails = $elasticsearch->getAllEmails();

        return EmailResource::collection($emails);
    }
}
