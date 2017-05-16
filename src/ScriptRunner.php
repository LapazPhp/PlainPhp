<?php
namespace Lapaz\PlainPhp;

use Lapaz\PlainPhp\Exception\ScriptNotFoundException;
use Lapaz\PlainPhp\Exception\ScriptNotSpecifiedException;

/**
 * ScriptRunner is external PHP script runner safer than `extract` and `require` way.
 */
class ScriptRunner
{
    const STATEMENT_REQUIRE = 'require %s';
    const STATEMENT_INCLUDE = '@include %s';

    /**
     * Target file name.
     *
     * @var string|null
     */
    protected $filename;

    /**
     * Execution statement template.
     *
     * @var string
     */
    protected $statement;

    /**
     * Variables extracted to script.
     *
     * @var array
     */
    protected $vars = [];

    /**
     * The object that evaluated by `$this` in script.
     *
     * @var object|null
     */
    protected $boundObject = null;

    /**
     * ScriptRunner constructor.
     * All parameters of this constructor are optional to determine them later.
     *
     * @param string|null $filename External PHP file name.
     * @param array $vars Key => value pair of variables extracted to script.
     * @param object|null $boundObject Object assumed as `$this` in evaluated script.
     */
    public function __construct($filename = null, $vars = [], $boundObject = null)
    {
        $this->filename = $filename;
        $this->statement = static::STATEMENT_REQUIRE;
        $this->vars = $vars;
        $this->boundObject = $boundObject;
    }

    /**
     * Utility factory to use runner instance like DSL.
     *
     * ```
     * ScriptRunner::which()->includes('some/file.php')->with(['param' => 1])->run();
     * ```
     *
     * @return static ScriptRunner instance.
     */
    public static function which()
    {
        return new static();
    }

    /**
     * Returns new instance to execute given file with `require` statement.
     *
     * @param string $filename target file name.
     * @return static Cloned instance of ScriptRunner.
     */
    public function requires($filename)
    {
        $that = clone $this;
        $that->filename = $filename;
        $that->statement = static::STATEMENT_REQUIRE;
        return $that;
    }

    /**
     * Returns new instance to execute given file with `@include` statement.
     *
     * @param string $filename target file name.
     * @return static Cloned instance of ScriptRunner.
     */
    public function includes($filename)
    {
        $that = clone $this;
        $that->filename = $filename;
        $that->statement = static::STATEMENT_INCLUDE;
        return $that;
    }

    /**
     * Returns new instance binding given object as script's $this variable.
     *
     * @param object $object Object assumed as `$this` in evaluated script.
     * @return static Cloned instance of ScriptRunner.
     */
    public function binding($object)
    {
        if (!($object === null || is_object($object))) {
            throw new \InvalidArgumentException('The object assumed to $this must be an object or null');
        }
        $that = clone $this;
        $that->boundObject = $object;
        return $that;
    }

    /**
     * Returns new instance having more variables.
     *
     * @param array $vars Key => value pair of variables extracted to script
     * @return static Cloned instance of ScriptRunner.
     */
    public function with(array $vars)
    {
        $that = clone $this;
        $that->vars = array_merge($that->vars, $vars);
        return $that;
    }

    /**
     * Executes the file and returns result.
     * If file was actually missing and execution statement without `@`, this method
     * throws `ScriptNotFoundException` (inherited from `RuntimeException`).
     *
     * @return mixed Result returned from external script.
     */
    public function run()
    {
        if ($this->filename === null) {
            throw new ScriptNotSpecifiedException('File was not specified.');
        }

        if ($this->statement[0] != '@' && !is_file($this->filename)) {
            throw new ScriptNotFoundException('File not exists: ' . $this->filename);
        }

        $__ = $this;

        $closure = function () use ($__) {
            extract($__->vars);
            $_ = null;
            eval('$_ = ' . sprintf($__->statement, '$__->filename') . ';');
            return $_;
        };

        if ($this->boundObject) {
            $closure = $closure->bindTo($this->boundObject);
        }

        return $closure();
    }
}
