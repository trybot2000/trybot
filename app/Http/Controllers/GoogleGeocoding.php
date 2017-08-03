<?php
namespace App\Http\Controllers;

use App\Http\Controllers\ClassHelper;
use Ixudra\Curl\Facades\Curl;

class GoogleGeocoding extends ClassHelper
{
    protected $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json?';
    protected $key;

    public $firstResult;

    public function __construct()
    {
        $this->key = config('services.google.geocoding');
    }

    public function search($address)
    {
        $key         = $this->key;
        $queryString = http_build_query(compact('address', 'key'));
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

            $this->firstResult = $response['results'][0];

            return [
                'location' => $this->firstResult['formatted_address'],
                'latlon'    => $this->getLatLon(),
            ];
        }
        return array(
            'status'  => 'error',
            'message' => 'the curl request failed',
            'data'    => $result,
        );
    }

    /**
     * Return the latitude and longitude, based on the results from search
     *
     * @return     string  A lat/lon pair, as a single string. Format: 35.6894875,139.6917064
     */
    public function getLatLon()
    {
        if (isset($this->firstResult) && (isset($this->firstResult['geometry']['location']['lat']))) {
            return trim($this->firstResult['geometry']['location']['lat']) . ',' . $this->firstResult['geometry']['location']['lng'];
        }

        return "";
    }
}
