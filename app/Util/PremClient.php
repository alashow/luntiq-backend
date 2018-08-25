<?php

namespace App\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

class PremClient
{
    /**
     * @var Client Guzzle client
     */
    private $httpClient;

    /**
     * PremClient constructor.
     */
    public function __construct()
    {
        $handler = HandlerStack::create();
        $handler->push(Middleware::mapRequest(function (RequestInterface $request) {
            $request = $request->withUri(Uri::withQueryValue($request->getUri(), 'customer_id', config('services.prem.id')));
            $request = $request->withUri(Uri::withQueryValue($request->getUri(), 'pin', config('services.prem.pin')));
            return $request;
        }));

        $this->httpClient = new Client([
            'base_uri' => 'https://www.premiumize.me/api/',
            'headers'  => [
                'User-Agent' => 'Luntiq v1.0.0',
            ],
            'handler'  => $handler,
        ]);
    }

    public function getClient()
    {
        return $this->httpClient;
    }
}
