# PlainPhp

[![Build Status](https://travis-ci.org/LapazPhp/PlainPhp.svg?branch=master)](https://travis-ci.org/LapazPhp/PlainPhp)

Plain PHP script file runner that safer than `expand()` and `require` way.


## Quick Start

```php
$config = \Lapaz\PlainPhp\ScriptRunner::create()->with([
    'parameter' => '...',
    'anotherParameter' => '...',
])->doRequire('path/to/config-file.php');
```

or

```php
$config = \Lapaz\PlainPhp\ScriptRunner::doRequireWithVars('path/to/config-file.php', [
    'parameter' => '...',
    'anotherParameter' => '...',
]);
```

`config-file.php`

```php
<?php
/** @var string $parameter Value passed by runner */

return [
    // your config
    'some-element' => $parameter,
    'another-element' => $anotherParameter,
];
```
## Features

- Closed and safer evaluation than raw `require` or `include`
- Binding any object to `$this` variable
- Immutable and branchable variable bound context
