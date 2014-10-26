<?php 

namespace Estey\Rubix;

/**
 * Mock the fopen() function.
 * @param string $file
 * @return string
 */
function fopen($file)
{
    return 'File Data.';
}

namespace Estey\Rubix\Test\Unit;

class ClientTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->client = \Mockery::mock('GuzzleHttp\Client');
        $this->request = \Mockery::mock('GuzzleHttp\Message\Request');
        $this->rubix = new \Estey\Rubix\Client(
            'token123',
            'v1',
            'http://www.foo.bar/',
            $this->client
        );
    }

    /**
     * Test  creating a new client.
     */
    public function testClientInit()
    {
        $client = new \Estey\Rubix\Client(
            'token123',
            'v1',
            'http://www.foo.bar'
        );
        $guzzle = $this->getInaccessible($client, 'client');

        $this->assertEquals(get_class($guzzle), 'GuzzleHttp\Client');
        $this->assertEquals($guzzle->getBaseUrl(), 'http://www.foo.bar/v1/');
        $headers = $guzzle->getDefaultOption('headers');
        $this->assertEquals($headers['user_key'], 'token123');
    }

    /**
     * Test listCategories() method.
     */
    public function testListCategories()
    {
        // The value of the protected $category variable should be null.
        $this->assertEquals(
            $this->getInaccessible($this->rubix, 'category'),
            null
        );

        // Mock category list data.
        $categories = [
            ['id' => 1],
            ['id' => 2]
        ];

        // Mock Category Model.
        $category = \Mockery::mock('Estey\Rubix\Models\Category');

        // Set the value of the protected $category variable
        // to the Mock Category Model.
        $this->setInaccessible($this->rubix, 'category', $category);
        $this->assertEquals(
            $this->getInaccessible($this->rubix, 'category'),
            $category
        );

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('categories', null)
            ->andReturn($this->request);

        $this->request
            ->shouldReceive('json')
            ->once()
            ->andReturn($categories);

        $category
            ->shouldReceive('getInstance')
            ->twice()
            ->andReturn($categories[0], $categories[1]);

        $categoriesList = $this->rubix->listCategories();
        
        $this->assertEquals($categoriesList, $categories);
    }

    /**
     * Test listPatterns() method.
     */
    public function testListPatterns()
    {
        $patterns = [
            ['id' => 1, 'label' => 'foo'],
            ['id' => 2, 'label' => 'bar']
        ];

        // Mock Pattern Model.
        $pattern = \Mockery::mock('Estey\Rubix\Models\Pattern');

        // Set the value of the protected $pattern variable
        // to the Mock Pattern Model.
        $this->setInaccessible($this->rubix, 'pattern', $pattern);
        $this->assertEquals(
            $this->getInaccessible($this->rubix, 'pattern'),
            $pattern
        );

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('patterns', ['query' => ['page' => 1]])
            ->andReturn($this->request);

        $this->request
            ->shouldReceive('json')
            ->once()
            ->andReturn($patterns);

        $pattern
            ->shouldReceive('getInstance')
            ->twice()
            ->andReturn($patterns[0], $patterns[1]);

        $patternsList = $this->rubix->listPatterns(1);

        $this->assertEquals($patternsList, $patterns);
    }

    /**
     * Test deletePattern() method.
     */
    public function testDeletePattern()
    {
        $this->client
            ->shouldReceive('delete')
            ->once()
            ->with('patterns/123', null)
            ->andReturn($this->request);

        $this->request
            ->shouldReceive('getBody')
            ->once()
            ->andReturn('true');

        $this->assertTrue($this->rubix->deletePattern(123));
    }

    /**
     * Test deletePattern() method failing.
     *
     * @expectedException Estey\Rubix\Exceptions\NotFoundException
     */
    public function testDeletePatternFails()
    {
        $this->client
            ->shouldReceive('delete')
            ->once()
            ->with('patterns/124', null)
            ->andReturn($this->request);

        $this->request
            ->shouldReceive('getBody')
            ->once()
            ->andThrow(new \Estey\Rubix\Exceptions\NotFoundException);

        $this->rubix->deletePattern(124);
    }

    /**
     * Test addPattern() method.
     */
    public function testAddPatternFails()
    {
        $new_pattern = ['id' => 1, 'label' => 'foo'];

        // Mock Pattern Model.
        $pattern = \Mockery::mock('Estey\Rubix\Models\Pattern');
        $file = \Mockery::mock('GuzzleHttp\Post\PostFileInterface');

        // Set the value of the protected $pattern variable
        // to the Mock Pattern Model.
        $this->setInaccessible($this->rubix, 'pattern', $pattern);
        $this->assertEquals(
            $this->getInaccessible($this->rubix, 'pattern'),
            $pattern
        );

        $file->shouldReceive('getContent')
            ->once()
            ->andReturn('filey stuff');

        $this->client
            ->shouldReceive('post')
            ->once()
            ->with('patterns', [
                'body' => [
                    'pattern[file]' => 'filey stuff',
                    'pattern[label]' => 'foobar',
                    'pattern[category_name]' => 'matching'
                ]
            ])->andReturn($this->request);

        $this->request
            ->shouldReceive('json')
            ->once()
            ->andReturn($new_pattern);

        $pattern
            ->shouldReceive('getInstance')
            ->once()
            ->with($new_pattern)
            ->andReturn($new_pattern);

        $create_pattern = $this->rubix->addPattern([
            'file' => $file,
            'label' => 'foobar',
            'category_name' => 'matching'
        ]);

        $this->assertEquals($create_pattern, $new_pattern);
    }

    /**
     * Test the featureMatching() method.
     */
    public function testFeatureMatching()
    {
        $file = \Mockery::mock('GuzzleHttp\Post\PostFileInterface');
        $mockData = [
            'file' => 'foo',
            'mr' => 0.9,
            'mma' => 150
        ];
        
        $file->shouldReceive('getContent')
            ->once()
            ->andReturn('foo');

        $this->client
            ->shouldReceive('post')
            ->once()
            ->with('patterns/feature_matcher', ['body' => $mockData])
            ->andReturn($this->request);

        $this->request
            ->shouldReceive('json')
            ->once()
            ->andReturn('bar');

        $response = $this->rubix->featureMatching([
            'file' => $file,
            'mr' => 0.9,
            'mma' => 150
        ]);

        $this->assertEquals($response, 'bar');
    }

    /**
     * Test the ocr() method.
     */
    public function testOcr()
    {
        $file = \Mockery::mock('GuzzleHttp\Post\PostFileInterface');
        
        $file->shouldReceive('getContent')
            ->once()
            ->andReturn('foo');

        $this->client
            ->shouldReceive('post')
            ->once()
            ->with('patterns/ocr', ['body' => ['file' => 'foo']])
            ->andReturn($this->request);

        $this->request
            ->shouldReceive('json')
            ->once()
            ->andReturn('bar');

        $response = $this->rubix->ocr(['file' => $file]);

        $this->assertEquals($response, 'bar');
    }

    /**
     * Test the request() method.
     */
    public function testRequest()
    {
        $this->client
            ->shouldReceive('post')
            ->once()
            ->with('foo', ['bar'])
            ->andReturn($this->request);

        $request = $this->callInaccessibleMethod(
            $this->rubix,
            'request',
            ['post', 'foo', ['bar']]
        );

        $this->assertEquals($this->request, $request);
    }

    /**
     * Test the request() method throwing an Exception.
     *
     * @expectedException Estey\Rubix\Exceptions\NotFoundException
     */
    public function testRequestException()
    {
        $this->client
            ->shouldReceive('post')
            ->once()
            ->with('foo', ['bar'])
            ->andThrow(new \Exception('', 404));

        $request = $this->callInaccessibleMethod(
            $this->rubix,
            'request',
            ['post', 'foo', ['bar']]
        );

        $this->assertEquals($this->request, $request);
    }

    /**
     * Test the handleException() method. Default.
     * 
     * @expectedException Estey\Rubix\Exceptions\ServiceException
     */
    public function testHandleServiceException()
    {
        $this->callInaccessibleMethod(
            $this->rubix,
            'handleException',
            [new \Exception('', 555)]
        );
    }

    /**
     * Test the handleException() method. Default.
     * 
     * @expectedException Estey\Rubix\Exceptions\ServiceException
     */
    public function testHandleDefaultException()
    {
        $this->callInaccessibleMethod(
            $this->rubix,
            'handleException',
            [new \Exception()]
        );
    }

    /**
     * Test the handleException() method. Default.
     * 
     * @expectedException Estey\Rubix\Exceptions\AuthorizationException
     */
    public function testHandleAuthorizationException()
    {
        $this->callInaccessibleMethod(
            $this->rubix,
            'handleException',
            [new \Exception('', 401)]
        );
    }

    /**
     * Test the handleException() method. Default.
     * 
     * @expectedException Estey\Rubix\Exceptions\ResponseException
     */
    public function testHandleResponseException()
    {
        $this->callInaccessibleMethod(
            $this->rubix,
            'handleException',
            [new \Exception('', 422)]
        );
    }

    /**
     * Test the handleException() method. Default.
     * 
     * @expectedException Estey\Rubix\Exceptions\NotFoundException
     */
    public function testHandleNotFoundException()
    {
        $this->callInaccessibleMethod(
            $this->rubix,
            'handleException',
            [new \Exception('', 404)]
        );
    }

    /**
     * Test the readFile() method.
     */
    public function testReadFile()
    {
        $file = \Mockery::mock('GuzzleHttp\Post\PostFileInterface');

        $file->shouldReceive('getContent')
            ->once()
            ->andReturn('bar');

        $readFile = $this->callInaccessibleMethod(
            $this->rubix,
            'readFile',
            [['id' => 1, 'foo' => $file], 'foo']
        );

        $this->assertEquals($readFile, ['id' => 1, 'foo' => 'bar']);
    }

    /**
     * Test the readFile() with no file in the array given.
     */
    public function testReadFileNoFile()
    {
        $readFile = $this->callInaccessibleMethod(
            $this->rubix,
            'readFile',
            [['id' => 1, 'foo' => 'bar']]
        );

        $this->assertEquals($readFile, ['id' => 1, 'foo' => 'bar']);
    }

    /**
     * Test the readFile() and make sure that the default second parameter
     * is always 'file'.
     */
    public function testReadFileDefaultKeyIsFile()
    {
        $file = \Mockery::mock('GuzzleHttp\Post\PostFileInterface');

        $file->shouldReceive('getContent')
            ->once()
            ->andReturn('bar');

        $readFile = $this->callInaccessibleMethod(
            $this->rubix,
            'readFile',
            [['id' => 1, 'file' => $file]]
        );

        $this->assertEquals($readFile, ['id' => 1, 'file' => 'bar']);
    }

    /**
     * Test the readFile() where file is wrong type.
     * 
     * @expectedException InvalidArgumentException
     */
    public function testReadFileNotString()
    {
        $readFile = $this->callInaccessibleMethod(
            $this->rubix,
            'readFile',
            [['id' => 1, 'file' => 1]]
        );
    }

    /**
     * Test the readFile() where file is a string.
     * Uses the mock of fopen() supplied above.
     */
    public function testReadFileWithStringFile()
    {
        $data = $this->callInaccessibleMethod(
            $this->rubix,
            'readFile',
            [['id' => 1, 'file' => 'path/to/file.jpg']]
        );

        $this->assertEquals($data['file'], 'File Data.');
        $this->assertEquals($data, ['id' => 1, 'file' => 'File Data.']);
    }
}
