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

    public function testDoRequireWithDifferentContext()
    {
        $prototypeRunner = ScriptRunner::create()->with(['foo' => 1]);

        $runner1 = $prototypeRunner->with(['bar' => 1]);
        $runner2 = $prototypeRunner->with(['bar' => 2]);
        $runner3 = $runner2->binding($this);

        $this->assertNotSame($prototypeRunner, $runner1);
        $this->assertNotSame($prototypeRunner, $runner2);
        $this->assertNotSame($prototypeRunner, $runner3);
        $this->assertNotSame($runner1, $runner2);
        $this->assertNotSame($runner1, $runner3);
        $this->assertNotSame($runner2, $runner3);

        $this->assertEquals([1, 1], $runner1->doRequire(__DIR__ . '/scripts/return-foo-bar.php'));
        $this->assertEquals([1, 2], $runner2->doRequire(__DIR__ . '/scripts/return-foo-bar.php'));
        $this->assertSame($this, $runner3->doRequire(__DIR__ . '/scripts/return-this.php'));
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
