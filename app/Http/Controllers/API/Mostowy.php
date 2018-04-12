<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JsonResponse;

class Mostowy extends Controller
{

    public function index()
    {

        $endpoints = [
            '/'         => [
                'method'    =>    'GET',
                'description'  => 'Base endpoint, which lists all available endpoints',
                'params'       => null,
                'requiresAuth' => '',
                'returnType'    =>    'JSON',
            ],
            '/auth'     => [
                'method'    =>    'GET',
                'description'  => 'The authorization endpoint, where you can get your API token',
                'params'       => 'yyyyy',
                'requiresAuth' => 'zzzzz',
                'returnType'    =>    'HTML',
            ],
            '/help'     => [
                'method'    =>    'GET',
                'description'  => 'xxxxx',
                'params'       => 'yyyyy',
                'requiresAuth' => 'zzzzz',
                'returnType'    =>    'HTML',
            ],
            '/parrot'   => [
                'method'    =>    'GET',
                'description'  => 'xxxxx',
                'params'       => 'yyyyy',
                'requiresAuth' => 'zzzzz',
                'returnType'    =>    'JSON',
            ],
            '/math/add' => [
                'method'    =>    'GET',
                'description'  => 'xxxxx',
                'params'       => 'yyyyy',
                'requiresAuth' => 'zzzzz',
                'returnType'    =>    'JSON',
            ],
        ];

        return JsonResponse::success($endpoints);
    }

    public function parrot()
    {
        $payload = \Request::all();

        return JsonResponse::success($payload);
    }

    public function help()
    {
        return \View::make('mostowy_help');
    }

}
