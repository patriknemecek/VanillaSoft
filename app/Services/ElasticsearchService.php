<?php

namespace App\Services;

use App\Utilities\Contracts\ElasticsearchHelperInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MailerLite\LaravelElasticsearch\Facade as Elasticsearch;

class ElasticsearchService implements ElasticsearchHelperInterface
{
    public const EMAILS_INDEX = 'emails';

    public function storeEmail(mixed $id, string $messageBody, string $messageSubject, string $toEmailAddress): mixed
    {
        $data = [
            'body' => [
                'email_address' => $toEmailAddress,
                'subject' => $messageSubject,
                'body' => $messageBody,
            ],
            'index' => static::EMAILS_INDEX,
            'id' => $id,
        ];

        return Elasticsearch::index($data);
    }

    public function getAllEmails(): Collection
    {
        $elasticStatResult = Elasticsearch::count([
            'index' => static::EMAILS_INDEX,
        ]);

        $elasticResult = Elasticsearch::search([
            'index' => static::EMAILS_INDEX,
            'size' => $elasticStatResult['count']
        ]);

        $hits = Arr::get($elasticResult, 'hits.hits', []);

        return collect($hits);
    }
}
