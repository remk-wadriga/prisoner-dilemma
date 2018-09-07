<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 15:13
 */

namespace App\Tests;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var JsonResponse
     */
    private $response;

    /**
     * @var Crawler
     */
    private $dom;

    public function __construct(Response $response, Crawler $crawler)
    {
        $this->response = $response;
        $this->dom = $crawler;

        $this->status = $response->getStatusCode();
        $this->content = $response->getContent();
        if (is_string($this->content) && !empty($this->content)) {
            try {
                $this->data = json_decode($this->content, true);
                if ($this->data === null) {
                    throw new \Exception(sprintf('Response body is not a valid json: %', $this->content));
                }
            } catch (\Exception $e) {
                throw new \Exception(sprintf('Can`t parse response body: %s', $e->getMessage()));
            }
        }
        $this->headers = $response->headers->all();
    }

    public function getHeader(string $name, $defaultValue = null)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : $defaultValue;
    }

    public function get(string $name, $defaultValue = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $defaultValue;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return JsonResponse
     */
    public function getResponse(): JsonResponse
    {
        return $this->response;
    }

    /**
     * @return Crawler
     */
    public function getDom(): Crawler
    {
        return $this->dom;
    }
}