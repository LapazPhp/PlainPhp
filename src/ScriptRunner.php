<?php
namespace Lapaz\PlainPhp;

/**
 * ScriptRunner is external PHP script runner safer than `extract` and `require` way.
 */
class ScriptRunner
{
    /**
     * The object that evaluated by `$this` in script.
     *
     * @var object|null
     */
    protected $boundedObject = null;

    /**
     * Variables extracted to script.
     *
     * @var array
     */
    protected $vars = [];

    /**
     * Simple static factory.
     *
     * @return static New instance of ScriptRunner.
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Returns new instance binding given object as script's $this variable.
     *
     * @param object $object Object assumed as `$this` in evaluated script.
     * @return static Cloned instance of ScriptRunner.
     */
    public function binding($object)
    {
        $that = clone $this;
        $that->boundedObject = $object;
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
     * Executes require statement and returns result.
     * If file was missing this method throws `InvalidArgumentException`.
     *
     * @param string $filename External PHP file name.
     * @return mixed Result returned from external script.
     */
    public function doRequire($filename)
    {
        return $this->_run($filename,'require %s');
    }

    /**
     * Executes include statement with `@` and returns result.
     * If file was missing this method returns `false`.
     *
     * @param string $filename External PHP file name.
     * @return mixed Result returned from external script.
     */
    public function doInclude($filename)
    {
        return $this->_run($filename,'@include %s');
    }

    /**
     * Internal implementation of require and include.
     *
     * @param string $filename External PHP file name.
     * @param string $statement `printf` form template to evaluated statement.
     * @return mixed Result returned from external script.
     */
    public function _run($filename, $statement = 'require %s')
    {
        $runner = function ($_statement_, $_filename_, $_vars_) {
            if ($_statement_[0] != '@' && !is_file($_filename_)) {
                throw new \InvalidArgumentException('File not exists: ' . $_filename_);
            }
            extract($_vars_);
            $_ = null;
            eval('$_ = ' . sprintf($_statement_, '$_filename_') . ';');
            return $_;
        };

        if ($this->boundedObject) {
            $runner = $runner->bindTo($this->boundedObject);
        }

        return $runner($statement, $filename, $this->vars);
    }

    /**
     * Utility method for instant running of `doRequire()`.
     *
     * @param string $filename External PHP file name.
     * @param array $vars Key => value pair of variables extracted to script
     * @param object|null $boundObject Object assumed as `$this` in evaluated script.
     * @return mixed Result returned from external script.
     */
    public static function doRequireWithVars($filename, $vars = [], $boundObject = null)
    {
        return static::create()->with($vars)->binding($boundObject)->doRequire($filename);
    }

    /**
     * Utility method for instant running of `doInclude()`.
     *
     * @param string $filename External PHP file name.
     * @param array $vars Key => value pair of variables extracted to script
     * @param object|null $boundObject Object assumed as `$this` in evaluated script.
     * @return mixed Result returned from external script.
     */
    public static function doIncludeWithVars($filename, $vars = [], $boundObject = null)
    {
        return static::create()->with($vars)->binding($boundObject)->doInclude($filename);
    }
}
