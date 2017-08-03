<?php
namespace App\Http\Controllers;

use App\Http\Controllers\ClassHelper;
use App\Http\Controllers\JsonResponse;
use Ixudra\Curl\Facades\Curl;
use pQuery;

class GoogleSearch extends ClassHelper
{
    protected $baseUrl = 'https://www.google.com/search?';

    public $status;
    public $errorMessage;
    public $errorCode;

    public function getSearch($query, $limit = 3)
    {
        $search = $this->search($query, $limit);
        switch ($this->status) {
            case 'fail':
                return JsonResponse::fail($search);
                break;

            case 'error':
                return JsonResponse::error($this->errorMessage, $this->errorCode, $search);
                break;

            default:
                return JsonResponse::success($search);
                break;
        }
    }

    public function isSuccess()
    {
        return $this->status == 'success';
    }

    public function search($query, $limit = 3)
    {
        $queryString = http_build_query(compact('query'));
        $requestUrl  = $this->baseUrl . $queryString;
        $result      = Curl::to($requestUrl)
            ->allowRedirect()
            ->returnResponseObject()
            ->get();

        if (!isset($result->error)) {

            $content = $result->content;

            // Parse the Google search results page
            $dom     = pQuery::parseStr($content);
            $links   = $dom->query('.g');
            $results = [];


            foreach ($links as $key => $resultItem) {
                $a = $resultItem->query('.r a');
                if (empty($a->attr('href'))) {
                    continue;
                }
                $results[$key]['href']  = $a->attr('href');
                $results[$key]['title'] = iconv('UTF-8', 'ASCII//TRANSLIT', $a->text());

                if (preg_match('/\/url\?q\=(https?.*)(?:&|&amp;)sa/i', $a->attr('href'), $parts)) {
                    $results[$key]['url'] = $parts[1];
                } else {
                    $results[$key]['url'] = null;
                }

                \Log::info($resultItem->query('.s .st')->html());
                $results[$key]['text']     = $this->cleanText($resultItem->query('.s .st')->html());
                $results[$key]['text_alt'] = $this->cleanText($resultItem->query('.st')->text());

                $results[$key]['image'] = $resultItem->query('img')->attr('src');
            }

            if (count($results) == 0) {
                return ['curl' => $result, 'results' => $results];
            }
            $data = [];
            foreach ($results as $k => $v) {
                if (empty($v['url'])) {
                    continue;
                }
                $data[] = array(
                    'name'            => $v['title'],
                    'description'     => $v['text'],
                    'description_alt' => $v['text_alt'],
                    'url'             => $v['url'],
                    'image'           => $v['image'],
                );
                if (count($data) >= $limit) {
                    break;
                }
            }

            \Log::info(json_encode($data));

            $this->status = 'success';
            return $data;
        }

        $this->status       = error;
        $this->errorMessage = 'The curl request failed';
        $this->errorCode    = 500;
        return $result;
    }

    public function cleanText($text)
    {
        // Strip most tags (except bold)
        $text = strip_tags($text, '<b>');

        // Fix encoding issues
        // $text = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($text));

        // Fix any bolds separated by a line break
        $text = preg_replace('/(\<\/b\>)(<br \/>|\\n|\s)+(\<b>)/i', " ", $text);        

        // Bold matches
        $text = str_ireplace(['<b>', '</b>'], '*', $text);

        // Fix bold markdown surrounded by single quotes
        $text = str_ireplace("'*", '"*', $text);
        $text = str_ireplace("*'", '*"', $text);

        // Remove line breaks
        $text = str_ireplace("\n", '', $text);

        return $text;
    }
}
