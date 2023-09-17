<?php

namespace PHPSTORM_META {

    registerArgumentsSet('HTTP_METHODS',
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'CONNECT',
        'TRACE');

    expectedArguments(\GuzzleHttp\Psr7\Request::__construct(), 0, argumentsSet('HTTP_METHODS'));
    expectedArguments(\GuzzleHttp\Client::request(), 0, argumentsSet('HTTP_METHODS'));
    expectedArguments(\GuzzleHttp\ClientInterface::request(), 0, argumentsSet('HTTP_METHODS'));
    expectedArguments(\GuzzleHttp\ClientTrait::request(), 0, argumentsSet('HTTP_METHODS'));
    expectedArguments(\GuzzleHttp\Client::requestAsync(), 0, argumentsSet('HTTP_METHODS'));
    expectedArguments(\GuzzleHttp\ClientInterface::requestAsync(), 0, argumentsSet('HTTP_METHODS'));
    expectedArguments(\GuzzleHttp\ClientTrait::requestAsync(), 0, argumentsSet('HTTP_METHODS'));
    expectedArguments(\Illuminate\Support\Facades\Http::send(), 0, argumentsSet('HTTP_METHODS'));
}
