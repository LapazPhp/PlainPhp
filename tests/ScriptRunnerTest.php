<?php
namespace Lapaz\PlainPhp;

class ScriptRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testWhichMethod()
    {
        $this->assertInstanceOf(ScriptRunner::class, ScriptRunner::which());
    }

    /**
     * @expectedException \Lapaz\PlainPhp\Exception\ScriptNotSpecifiedException
     */
    public function testTargetFileNameUnspecified()
    {
        (new ScriptRunner())->run();
    }

    /**
     * @expectedException \Lapaz\PlainPhp\Exception\ScriptNotFoundException
     */
    public function testRequireMissingFile()
    {
        ScriptRunner::which()->requires(__DIR__ . '/scripts/__missing-file__.php')->run();
        $this->fail();
    }

    public function testIncludeMissingFile()
    {
        $result = ScriptRunner::which()->includes(__DIR__ . '/scripts/__missing-file__.php')->run();
        $this->assertFalse($result);
    }

    public function testWithVars()
    {
        $result = ScriptRunner::which()->requires(__DIR__ . '/scripts/return-foo-bar.php')->with([
            'foo' => 1,
            'bar' => 2,
        ])->run();
        $this->assertEquals([1, 2], $result);
    }

    public function testWithObject()
    {
        $result = ScriptRunner::which()->requires(__DIR__ . '/scripts/return-this.php')->binding($this)->run();
        $this->assertSame($this, $result);
    }

    public function testBranchingContext()
    {
        $prototypeRunner = (new ScriptRunner())->with(['foo' => 1]);

        $runner1 = $prototypeRunner->with(['bar' => 1]);
        $runner2 = $prototypeRunner->with(['bar' => 2]);
        $runner3 = $runner2->binding($this);

        $this->assertNotSame($prototypeRunner, $runner1);
        $this->assertNotSame($prototypeRunner, $runner2);
        $this->assertNotSame($prototypeRunner, $runner3);
        $this->assertNotSame($runner1, $runner2);
        $this->assertNotSame($runner1, $runner3);
        $this->assertNotSame($runner2, $runner3);

        $this->assertEquals([1, 1], $runner1->requires(__DIR__ . '/scripts/return-foo-bar.php')->run());
        $this->assertEquals([1, 2], $runner2->requires(__DIR__ . '/scripts/return-foo-bar.php')->run());
        $this->assertSame($this, $runner3->requires(__DIR__ . '/scripts/return-this.php')->run());
    }
}
