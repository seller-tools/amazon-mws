<?php
/**
 * Created by PhpStorm.
 * User: nikolai5
 * Date: 5/11/18
 * Time: 12:30 PM
 */

namespace MCS;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class MWSRequest extends Request
{
    private $parseCallback;
    public function __construct(string $method, $uri, array $headers = [], $body = null, callable $parseCallback, string $version = '1.1')
    {
        parent::__construct($method, $uri, $headers, $body, $version);
        $this->parseCallback = $parseCallback;
    }

    public function parseResponse(Response $response) {
        return call_user_func($this->parseCallback, $response);
    }


    function getParseCallback() : callable {
        return $this->parseCallback;
    }

    function setParseCallback(callable $parseCallback) : MWSRequest {
        $this->parseCallback = $parseCallback;
        return $this;
    }
}