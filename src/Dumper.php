<?php

/*
 *
 * Miva Merchant
 *
 * This file and the source codes contained herein are the property of
 * Miva, Inc. Use of this file is restricted to the specific terms and
 * conditions in the License Agreement associated with this file. Distribution
 * of this file or portions of this file for uses not covered by the License
 * Agreement is not allowed without a written agreement signed by an officer of
 * Miva, Inc.
 *
 * Copyright 1998-2025 Miva, Inc. All rights reserved.
 * https://www.miva.com
 *
 */

namespace pdeans\Debuggers\Vardumper;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class Dumper
{
    /**
     * Dump the given value.
     *
     * @param mixed $value The value to dump.
     * @param string $label Displays the provided label above the dumped value.
     * @param bool $showOutputSource Include the output source display with the dumped value.
     * @return void
     */
    public function dump(mixed $value, string $label = '', bool $showOutputSource = true): void
    {
        $is_cli = in_array(PHP_SAPI, ['cli', 'phpdbg'], true);

        if ($label !== '') {
            echo $label, $is_cli ? PHP_EOL : '<br>';
        }

        if (class_exists(CliDumper::class)) {
            $cloner = new VarCloner();

            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

            $data = $cloner->cloneVar($value);

            $dumper = $is_cli ? new CliDumper() : new HtmlDumper();

            if ($showOutputSource && $dumper instanceof HtmlDumper) {
                $dumper->dumpWithSource($data);
            } else {
                $dumper->dump($data);
            }
        } else {
            var_dump($value);
        }
    }
}
