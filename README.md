## Prettier Var Dumper

Better looking and functioning alternative to PHP's native `var_dump()`. Built on top of Symfony's [VarDumper](https://symfony.com/doc/current/components/var_dumper.html) component, with a Github inspired color theme.

### Installation

Install via [Composer](https://getcomposer.org/).

```
$ composer require pdeans/debuggers-vardumper
```

### Usage

Create a new instance of the `Dumper` class, and pass any variable to the `dump()` method for output via the browser or cli. The `dump()` method accepts an optional second paramater to add a label for the output.

```php
use pdeans\Debuggers\Vardumper\Dumper;

$dumper = new Dumper();

$arr = [1,2,3];
$dumper->dump($arr);

// With label
$dumper->dump($arr, 'array values');

// With label and suppress the source output information (file:line number)
$dumper->dump($arr, 'array values', false);
```


