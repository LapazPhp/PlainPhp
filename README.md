# PlainPhp

[![Build Status](https://travis-ci.org/LapazPhp/PlainPhp.svg?branch=master)](https://travis-ci.org/LapazPhp/PlainPhp)

Plain PHP script file runner that safer than `extract()` and `require` way.


## Quick Start

To load these `config-file.php` script:

```php
<?php
/* @var $this SomeObject */

return [
    // your config
    'some-element' => $parameter,
    'another-element' => $anotherParameter,
    'element-by-method-call' => $this->getConfigElement(),
];
```

Use ScriptRunner below instead of raw `require` statement.

```php
$config = ScriptRunner::which()->requires('path/to/config-file.php')->with([
    'parameter' => '...',
    'anotherParameter' => '...',
])->binding($someObject)->run();
```

## Features

- Closed and safer evaluation than raw `require` or `include`
- Binding any object as `$this` variable in target file
- Immutable and branchable variable bound context
