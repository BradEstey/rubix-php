<?php 

namespace Estey\Rubix\Test\Unit\Models;

use Mockery as m;
use Estey\Rubix\Models\Pattern;
use Estey\Rubix\Test\Unit\TestCase;

class PatternTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->stub = new Pattern;
    }

    /**
     * Test getInstance() method.
     */
    public function testGetInstance()
    {
        $pattern = $this->callInaccessibleMethod(
            $this->stub,
            'getInstance',
            [['id' => 1, 'label' => 'foobar']]
        );

        $this->assertEquals(get_class($pattern), 'Estey\Rubix\Models\Pattern');
        $this->assertEquals($pattern->getAttribute('id'), 1);
        $this->assertEquals($pattern->id, 1);
    }

    /**
     * Test setData() method.
     */
    public function testSetData()
    {
        $this->stub->setData(['id' => 1, 'foo' => 'bar', 'label' => 'foobar']);
        $this->assertEquals($this->stub->getAttribute('id'), 1);
        $this->assertEquals($this->stub->id, 1);
        $this->assertEquals($this->stub->getAttribute('label'), 'foobar');
        $this->assertEquals($this->stub->label, 'foobar');
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
    }

    /**
     * Test the toArray() method.
     */
    public function testToArray()
    {
        $this->stub->setData(['id' => 1, 'foo' => 'bar', 'label' => 'foobar']);
        $this->assertEquals(
            $this->stub->toArray(),
            ['id' => 1, 'label' => 'foobar']
        );
    }

    /**
     * Test the toJson() method.
     */
    public function testToJson()
    {
        $this->stub->setData(['id' => 1, 'foo' => 'bar', 'label' => 'foobar']);
        $this->assertEquals(
            $this->stub->toJson(),
            '{"id":1,"label":"foobar"}'
        );
        $this->assertEquals($this->stub->toJson(), (string) $this->stub);
    }
}
