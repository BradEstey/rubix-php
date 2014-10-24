<?php 

namespace Estey\Rubix\Test\Unit\Models;

use Mockery as m;
use Estey\Rubix\Models\Category;
use Estey\Rubix\Test\Unit\TestCase;

class CategoryTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->stub = new Category;
    }

    /**
     * Test getInstance() method.
     */
    public function testGetInstance()
    {
        $category = $this->callInaccessibleMethod(
            $this->stub,
            'getInstance',
            [['id' => 1, 'title' => 'foobar']]
        );

        $this->assertEquals(get_class($category), 'Estey\Rubix\Models\Category');
        $this->assertEquals($category->getAttribute('id'), 1);
        $this->assertEquals($category->id, 1);
    }

    /**
     * Test setData() method.
     */
    public function testSetData()
    {
        $this->stub->setData(['id' => 1, 'foo' => 'bar', 'title' => 'foobar']);
        $this->assertEquals($this->stub->getAttribute('id'), 1);
        $this->assertEquals($this->stub->id, 1);
        $this->assertEquals($this->stub->getAttribute('title'), 'foobar');
        $this->assertEquals($this->stub->title, 'foobar');
        $this->assertEquals($this->stub->getAttribute('foo'), null);
        $this->assertEquals($this->stub->foo, null);
    }

    /**
     * Test setAttribute() method.
     */
    public function testSetAttribute()
    {
        $this->stub->setAttribute('id', 1);
        $this->assertEquals($this->stub->getAttribute('id'), 1);
        $this->assertEquals($this->stub->id, 1);

        $this->stub->setAttribute('id', 55);
        $this->assertEquals($this->stub->getAttribute('id'), 55);
        $this->assertEquals($this->stub->id, 55);

        $this->stub->setAttribute('foo', 'bar');
        $this->assertEquals($this->stub->getAttribute('foo'), null);
        $this->assertEquals($this->stub->foo, null);

        $this->stub->id = 19;
        $this->assertEquals($this->stub->getAttribute('id'), 19);
        $this->assertEquals($this->stub->id, 19);

        $this->stub->created_at = '2014-05-08T20:44:06.551Z';
        $this->assertEquals(
            get_class($this->stub->created_at),
            'Carbon\Carbon'
        );
        $this->assertEquals($this->stub->created_at->year, 2014);
        $this->assertEquals($this->stub->created_at->month, 5);
        $this->assertEquals($this->stub->created_at->day, 8);
    }

    /**
     * Test the toArray() method.
     */
    public function testToArray()
    {
        $this->stub->setData(['id' => 1, 'foo' => 'bar', 'title' => 'foobar']);
        $this->assertEquals(
            $this->stub->toArray(),
            ['id' => 1, 'title' => 'foobar']
        );
    }

    /**
     * Test the toJson() method.
     */
    public function testToJson()
    {
        $this->stub->setData(['id' => 1, 'foo' => 'bar', 'title' => 'foobar']);
        $this->assertEquals(
            $this->stub->toJson(),
            '{"id":1,"title":"foobar"}'
        );
        $this->assertEquals($this->stub->toJson(), (string) $this->stub);
    }
}
