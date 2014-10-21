<?php

namespace Estey\Rubix;

use Estey\Rubix\Exceptions\AuthorizationException;
use Estey\Rubix\Exceptions\ResponseException;
use Estey\Rubix\Exceptions\NotFoundException;
use Estey\Rubix\Exceptions\ServiceException;
use Estey\Rubix\Models\Pattern;
use Estey\Rubix\Models\Category;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Post\PostFileInterface;
use GuzzleHttp\Post\PostFile;
use Exception;
use InvalidArgumentException;

class Client
{
    /**
     * GuzzleHttp Client.
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * New Rubix Client Class.
     * 
     * @param string $access_token
     * @param string $version
     * @param string $domain
     * @param GuzzleHttp\Client $client
     * @return void
     */
    public function __construct(
        $access_token,
        $version = 'v1',
        $domain = 'http://api.rubix.io',
        GuzzleHttp $client = null
    ) {
        $this->client = $client ?: new GuzzleHttp([
            'base_url' => [
                $domain . '/api/{version}/', ['version' => $version]
            ],
            'defaults' => [
                'headers' => ['user_key' => $access_token]
            ]
        ]);
    }

    /**
     * List categories.
     *
     * @param Estey\Rubix\Models\Category $category
     * @return Estey\Rubix\Models\Category[]
     */
    public function listCategories(Category $category = null)
    {
        $categories = $this->request('get', 'categories')->json();

        return array_map(function ($category) {
            return $this->makeCategory($category);
        }, $categories);
    }

    /**
     * Make a new Category model instance.
     * 
     * @param array $data
     * @param Estey\Rubix\Models\Category $category
     * @return Estey\Rubix\Models\Category
     */
    protected function makeCategory($data, Category $category = null)
    {
        $category = $category ?: new Category;
        return $category->getInstance($data);
    }

    /**
     * List patterns.
     *
     * @param integer $page
     * @return Estey\Rubix\Models\Pattern[]
     */
    public function listPatterns($page = 1)
    {
        $patterns = $this->request('get', 'patterns', [
            'query' => ['page' => $page]
        ])->json();

        return array_map(function ($pattern) {
            return $this->makePattern($pattern);
        }, $patterns);
    }

    /**
     * Delete pattern. Returns true if pattern is deleted
     * successfully. Throws a NotFoundException if
     * pattern doesn't exist.
     *
     * @param integer $pattern_id
     * @return boolean
     * @throws Estey\Rubix\Exceptions\NotFoundException
     */
    public function deletePattern($pattern_id)
    {
        return (boolean) $this->request(
            'delete',
            'patterns/' . $pattern_id
        )->getBody();
    }

    /**
     * Add pattern.
     * [ 
     *   file: 'path/to/file',
     *   remote_file_url: "http://example.com/path/to/image",
     *   label: "uid",
     *   category_name: "matching"
     * ]
     *
     * @param array $data
     * @return Estey\Rubix\Models\Pattern
     */
    public function addPattern($data)
    {
        $data = $this->readFile($data);

        foreach ($data as $key => $value) {
            $data['pattern[' . $key . ']'] = $value;
            unset($data[$key]);
        }

        $response = $this->request('post', 'patterns', [
            'body' => $data
        ])->json();

        return $this->makePattern($response);
    }

    /**
     * Make a new Pattern model instance.
     * 
     * @param array $data
     * @param Estey\Rubix\Models\Pattern $pattern
     * @return Estey\Rubix\Models\Pattern
     */
    protected function makePattern($data, Pattern $pattern = null)
    {
        $pattern = $pattern ?: new Pattern;
        return $pattern->getInstance($data);
    }

     /**
     * Feature matching.
     * [ 
     *   file: 'path/to/file',
     *   remote_file_url: "http://example.com/path/to/scene",
     *   mr: 0.9,
     *   mma: 150
     * ]
     *
     * @param array $data
     * @return array
     */
    public function featureMatching($data)
    {
        $data = $this->readFile($data);
        return $this->request('post', 'patterns/feature_matcher', [
            'body' => $data
        ])->json();
    }

     /**
     * OCR.
     * [ 
     *   file: 'path/to/file',
     *   remote_file_url: "http://example.com/path/to/scene",
     *   rectangles: [[x1,y1,x2,y2],..,[x1,y1,x2,y2]]
     * ]
     *
     * @param array $data
     * @return array
     */
    public function ocr($data)
    {
        $data = $this->readFile($data);
        return $this->request('post', 'patterns/ocr', [
            'body' => $data
        ])->json();
    }

    /**
     * Send API request and send exceptions to handleException() method.
     * 
     * @param string $method get, post, put, delete.
     * @param string $resource
     * @param array $data
     * @return GuzzleHttp\Message\Response
     */
    protected function request($method, $resource, $data = [])
    {
        try {
            $response = $this->client->{$method}($resource, $data);
        } catch (Exception $e) {
            return $this->handleException($e);
        }

        return $response;
    }

    /**
     * Throw the appropriate exception.
     *
     * @param Exception $e
     * @return void
     * @throws Estey\Rubix\Exceptions\AuthorizationException
     * @throws Estey\Rubix\Exceptions\ResponseException
     * @throws Estey\Rubix\Exceptions\NotFoundException
     * @throws Estey\Rubix\Exceptions\ServiceException
     */
    protected function handleException(Exception $e)
    {
        switch ($e->getCode()) {
            case 401:
                throw new AuthorizationException($e->getMessage());
                break;
            case 422:
                throw new ResponseException($e->getMessage());
                break;
            case 404:
                throw new NotFoundException($e->getMessage());
                break;
            case 500:
            default:
                throw new ServiceException($e->getMessage());
        }
    }

    /**
     * Get the file in the given data array (if one exists) and 
     * replace it with an instance of a GuzzleHttp\Post\PostFile object.
     * Unless it's already an object implementation of 
     * GuzzleHttp\Post\PostFileInterface.
     * 
     * @param array $data
     * @param string $key
     * @return array
     * @throws InvalidArgumentException
     */
    private function readFile($data, $key = 'file')
    {
        if (isset($data[$key])) {
            $file = $data[$key];

            if (!$file instanceof PostFileInterface) {
                // $file must be a string or implement PostFileInterface
                if (!is_string($file)) {
                    throw new InvalidArgumentException(
                        'File must be a string or an implementation of ' .
                        'GuzzleHttp\Post\PostFileInterface'
                    );
                }

                $file = new PostFile($key, fopen($file, 'r'));
            }

            $data[$key] = $file->getContent();
        }
        return $data;
    }
}
