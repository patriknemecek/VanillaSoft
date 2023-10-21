<?php

namespace App\Services;

use App\Utilities\Contracts\ElasticsearchHelperInterface;
use MailerLite\LaravelElasticsearch\Facade as Elasticsearch;

class ElasticsearchService implements ElasticsearchHelperInterface
{
    public function storeEmail(mixed $id, string $messageBody, string $messageSubject, string $toEmailAddress): mixed
    {
        $data = [
            'body' => [
                'email_address' => $toEmailAddress,
                'subject' => $messageSubject,
                'body' => $messageBody,
            ],
            'index' => 'emails',
            'id' => $id,
        ];

        return Elasticsearch::index($data);
    }
}
