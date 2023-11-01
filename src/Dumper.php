<?php

namespace pdeans\Debuggers\Vardumper;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class Dumper
{
    public function dump($value, $label = '')
    {
        $is_cli = in_array(PHP_SAPI, ['cli', 'phpdbg'], true) ? true : false;

        if ($label !== '') {
            echo $label, ($is_cli ? PHP_EOL : '<br>');
        }

        if (class_exists(CliDumper::class)) {
            $cloner = new VarCloner();

            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

            $data = $cloner->cloneVar($value);

            $dumper = ($is_cli ? new CliDumper() : new HtmlDumper());

            if ($dumper instanceof HtmlDumper) {
                $dumper->dumpWithSource($data);
            } else {
                $dumper->dump($data);
            }
        } else {
            var_dump($value);
        }
    }
}
