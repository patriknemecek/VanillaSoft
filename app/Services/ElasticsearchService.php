<?php

namespace App\Services;

use App\Utilities\Contracts\ElasticsearchHelperInterface;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
        try {
            $elasticStatResult = Elasticsearch::count([
                'index' => static::EMAILS_INDEX,
            ]);

            $elasticResult = Elasticsearch::search([
                'index' => static::EMAILS_INDEX,
                'size' => $elasticStatResult['count']
            ]);

            $hits = Arr::get($elasticResult, 'hits.hits', []);

            return collect($hits);
        } catch (Missing404Exception $exception) {
            Log::warning(vsprintf('%s | %s', [
                __METHOD__,
                $exception
            ]));

            return collect([]);
        }
    }
}
