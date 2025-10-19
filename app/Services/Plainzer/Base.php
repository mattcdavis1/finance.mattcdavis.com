<?php

namespace App\Services\Plainzer;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class Base
{
  protected $baseUrl = 'https://api.plainzer.com';
  protected $client;
  protected $path = '/';

  public function __construct()
  {
    $this->client = new Client();
  }

  public function request(
    $url = null,
    $method = 'get',
    $options = [],
  ) : ?stdClass {
    $requestUrl = $url ?? $this->baseUrl . $this->path;
    $requestOptions['headers'] = $options['headers'] ?? [];
    $requestOptions['headers']['Authorization'] = 'Bearer ' . env('PLAINZER_API_KEY');

    if (!isset($requestOptions['headers']['Accept'])) {
      $requestOptions['headers']['Accept'] = 'application/json';
    }

    try {
      $response = $this->client->request(
        $method,
        $requestUrl,
        $requestOptions
      );
      $body = $response->getBody()->getContents();
      $json = json_decode($body);

      return (object) [
        'success' => true,
        'code' => $response->getStatusCode(),
        'result' => $json,
      ];
    } catch (RequestException $e) {
      $result = (object) [
        'success' => false,
        'code' => $e->getResponse()->getStatusCode(),
        'error' => $e->getMessage(),
        'result' => null,
      ];

      if ($e->hasResponse()) {
        $response = $e->getResponse();
        $result->code = $response->getStatusCode();
        $result->result . $response->getBody()->getContents();
      }

      return $result;
    }
  }
}
