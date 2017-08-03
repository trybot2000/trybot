<?php
namespace App\Http\Controllers;

use App\Http\Controllers\ClassHelper;
use Ixudra\Curl\Facades\Curl;

class KnowledgeGraph extends ClassHelper
{
    protected $baseUrl = 'https://kgsearch.googleapis.com/v1/entities:search?';
    protected $key;
    protected $kg                   = 'http://g.co/kg';
    protected $resultScoreThreshold = 200;

    public function __construct()
    {
        $this->key = config('services.google.knowledge_graph');
    }

    public function search($query, $limit = 1, $types = null)
    {
        $key         = $this->key;
        $queryString = http_build_query(compact('query', 'limit', 'types', 'key'));
        $requestUrl  = $this->baseUrl . $queryString;
        $result      = Curl::to($requestUrl)
            ->returnResponseObject()
        // ->asJson()
            ->get();

        if (!isset($result->error)) {
            $response = json_decode($result->content, true);
            if (count($response['itemListElement']) == 0) {
                return array(
                    'status' => 'fail',
                    'data'   => array(
                        'noResults' => 'No results were returned',
                        'response'  => $response,
                    ),
                );
            }
            $resultScore = $response['itemListElement'][0]['resultScore'];
            \Log::info("resultScore: " . $resultScore);
            if ($resultScore < $this->resultScoreThreshold) {
                return array(
                    'status' => 'fail',
                    'data'   => array(
                        'noResults' => 'Results fell below minimum threshold for resultScore of ' . $this->resultScoreThreshold,
                        'response'  => $response,
                    ),
                );
            }
            $item = $response['itemListElement'][0]['result'];

            return array(
                'status' => 'success',
                'data'   => array(
                    'name'                => $item['name'],
                    'description'         => $item['description'],
                    'detailedDescription' => isset($item['detailedDescription']) ? $item['detailedDescription'] : null,
                    'url'                 => isset($item['url']) ? $item['url'] : (isset($item['detailedDescription']['url']) ? $item['detailedDescription']['url'] : null),
                    'moreInfoUrl'         => $this->kg . str_ireplace('kg:', '', $item['@id']),
                    'resultScore'         => $resultScore,
                    'image'               => isset($item['image']) ? $item['image']['contentUrl'] : null,
                ),
            );

            return $response;
        }
        return array(
            'status'  => 'error',
            'message' => 'the curl request failed',
            'data'    => $result,
        );
    }
}
