<?php
namespace Lapaz\PlainPhp;

class ScriptRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testDoRequire()
    {
        $result = ScriptRunner::create()->doRequire(__DIR__ . '/scripts/return-1.php');
        $this->assertEquals(1, $result);
    }

    public function testDoInclude()
    {
        $result = ScriptRunner::create()->doInclude(__DIR__ . '/scripts/return-1.php');
        $this->assertEquals(1, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^File not exists:/
     */
    public function testDoRequireMissing()
    {
        ScriptRunner::create()->doRequire(__DIR__ . '/scripts/__missing-file__.php');
        $this->fail();
    }

    public function testDoIncludeMissing()
    {
        $result = ScriptRunner::create()->doInclude(__DIR__ . '/scripts/__missing-file__.php');
        $this->assertFalse($result);
    }

    public function testDoRequireWithVars()
    {
        $result = ScriptRunner::create()->with([
            'foo' => 1,
            'bar' => 2,
        ])->doRequire(__DIR__ . '/scripts/return-foo-bar.php');
        $this->assertEquals([1, 2], $result);
    }

    public function testDoIncludeWithVars()
    {
        $result = ScriptRunner::create()->with([
            'foo' => 1,
            'bar' => 2,
        ])->doInclude(__DIR__ . '/scripts/return-foo-bar.php');
        $this->assertEquals([1, 2], $result);
    }

    public function testDoRequireWithBoundObject()
    {
        $result = ScriptRunner::create()->binding($this)->doRequire(__DIR__ . '/scripts/return-this.php');
        $this->assertSame($this, $result);
    }

    public function testDoIncludeWithBoundObject()
    {
        $result = ScriptRunner::create()->binding($this)->doInclude(__DIR__ . '/scripts/return-this.php');
        $this->assertSame($this, $result);
    }
}
