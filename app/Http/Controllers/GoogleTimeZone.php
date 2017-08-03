<?php
namespace App\Http\Controllers;

use App\Http\Controllers\ClassHelper;
use Ixudra\Curl\Facades\Curl;

class GoogleTimeZone extends ClassHelper
{
    protected $baseUrl = 'https://maps.googleapis.com/maps/api/timezone/json?';
    protected $key;

    public $rawOffset;
    public $timeZoneId;

    public function __construct()
    {
        $this->key = config('services.google.time_zone_api');
    }

    public function search($location, $timestamp = null)
    {
        $key = $this->key;
        if (!$timestamp) {
            $timestamp = time();
        }
        $queryString = http_build_query(compact('location', 'timestamp', 'key'));
        $requestUrl  = $this->baseUrl . $queryString;

        $result = Curl::to($requestUrl)
            ->returnResponseObject()
            ->get();

        if (!isset($result->error)) {
            $response = json_decode($result->content, true);
            if ($response['status'] != 'OK') {
                return array(
                    'status' => 'fail',
                    'data'   => array(
                        'noResults' => 'No results were returned',
                        'response'  => $response,
                    ),
                );
            }

            $this->rawOffset  = $response['rawOffset'];
            $this->timeZoneId = $response['timeZoneId'];

            return $response;
        }
        return array(
            'status'  => 'error',
            'message' => 'the curl request failed',
            'data'    => $result,
        );
    }

    /**
     * Return the local time, based on the time zone information returned from
     * search.
     *
     * @return     string  The local time for the time zone. Format: 7:35 pm PDT, Fri May 5th
     */
    public function getLocalTime()
    {
        if ((is_null($this->rawOffset)) || (is_null($this->timeZoneId))) {
            return "";
        }
        $format = 'g:i a T, D M jS';

        $dateTime = new \DateTime();
        $dateTime->setTimeZone(new \DateTimeZone($this->timeZoneId));

        return $dateTime->format($format);
    }
}
