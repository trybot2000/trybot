<?php

namespace App\Http\Controllers;

use ApiAi\Client;
use ApiAi\Method\QueryApi;
use ApiAi\Model\Query;
use App\Http\Controllers\Controller;

class ApiAi extends Controller
{
    public function query()
    {
        $payload = \Request::all();

        if (!isset($payload['text'])) {
            return null;
        }
        return $this->getQuery($payload['text']);
    }

    public function getQuery($text, $sessionId = null)
    {
        $response = null;
        try {
            $client   = new Client(config('services.api_ai.trybot'));
            $queryApi = new QueryApi($client);

            $meaning = $queryApi->extractMeaning('TryBot weather 75070', [
                'lang'      => 'en',
                'sessionId' => '12345',
            ]);
            $response = new Query($meaning);
        } catch (\Exception $error) {
            return $error->getMessage();
        }

        return $response;
    }
}
