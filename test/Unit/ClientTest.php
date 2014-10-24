<?php 

namespace Estey\Rubix\Test\Unit;

use Mockery as m;
use Estey\Rubix\Client;

class ClientTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->client = m::mock('GuzzleHttp\Client');
        $this->request = m::mock('GuzzleHttp\Message\Request');
        $this->rubix = new Client(
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
        $client = new Client('token123', 'v1', 'http://www.foo.bar');
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
        $category = m::mock('Estey\Rubix\Models\Category');

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
        $pattern = m::mock('Estey\Rubix\Models\Pattern');

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
        $pattern = m::mock('Estey\Rubix\Models\Pattern');
        $file = m::mock('GuzzleHttp\Post\PostFileInterface');

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
}
