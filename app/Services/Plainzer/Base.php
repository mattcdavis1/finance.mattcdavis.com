<?php

namespace App\Services\Plainzer;

use App\Services\Util\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use stdClass;

class Base
{
  protected $baseUrl = 'https://api.plainzer.com';
  protected $client;
  protected $logger;
  protected $path = '/';

  public function __construct()
  {
    $this->client = new Client();
    $this->logger = new Logger();
  }

  public function request(
    $method = 'get',
    $url = null,
    $options = [],
  ) : ?stdClass {
    $requestUrl = $url ?? $this->baseUrl . $this->path;
    $requestOptions = $options ?? [];

    empty($requestOptions['headers']) && $requestOptions['headers'] = [];
    $requestOptions['headers']['Authorization'] = 'Bearer ' . env('PLAINZER_API_KEY');

    if (!isset($requestOptions['headers']['Accept'])) {
      $requestOptions['headers']['Accept'] = '*/*';
    }

    if (!isset($requestOptions['headers']['Content-Type'])) {
      $requestOptions['headers']['Content-Type'] = 'application/json';
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
        $result->result = $response->getBody()->getContents();
      }

      sleep(1);

      return $result;
    }
  }

  public function setLogger($logger)
  {
    $this->logger = $logger;
  }
}
