<?php

namespace App\Utilities\Contracts;

interface ElasticsearchHelperInterface
{
    /**
     * Store the email's message body, subject and to address inside elasticsearch.
     *
     * @param mixed $id
     * @param string $messageBody
     * @param string $messageSubject
     * @param string $toEmailAddress
     * @return mixed - Return the id of the record inserted into Elasticsearch
     */
    public function storeEmail(mixed $id, string $messageBody, string $messageSubject, string $toEmailAddress): mixed;
}
