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

use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\HtmlDumper as SymfonyHtmlDumper;

class HtmlDumper extends SymfonyHtmlDumper
{
    /**
     * The location of the source on expanded dumps.
     *
     * @var string
     */
    protected const EXPANDED_SEPARATOR = 'class=sf-dump-expanded>';

    /**
     * The location of the source on non-expanded dumps.
     *
     * @var string
     */
    protected const NON_EXPANDED_SEPARATOR = "\n</pre><script>";

    /**
     * List of files that require special trace handling mapped to their levels.
     *
     * @var array
     */
    protected static array $adjustableTraces = [
        'debuggers-vardumper/src/Dumper.php' => 1,
        'symfony/var-dumper/Resources/functions/dump.php' => 1,
    ];

    /**
     * Determines if the dumper is currently dumping.
     *
     * @var bool
     */
    protected bool $dumping = false;

     /**
     * The dump source resolver.
     *
     * @var callable|bool|null
     */
    protected static mixed $dumpSourceResolver = null;

    /**
     * The source output color.
     *
     * @var string
     */
    protected static string $sourceOutputColor = '#6E7781';

    /**
     * Create a new html dumper instance.
     */
    public function __construct(mixed $output = null, string|null $charset = null, int $flags = 0)
    {
        parent::__construct($output, $charset, $flags);

        $this->setStyles([
            'default' => 'background-color:#fff; color:#24292e; line-height:1.428571429; font-weight:normal; font:12px Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:100000',
            'num' => 'color:#005cc5',
            'const' => 'color:#005cc5',
            'str' => 'color:#032f62',
            'cchr' => 'color:#008000',
            'note' => 'color:#6f42c1',
            'ref' => 'color:#888',
            'public' => 'color:#d73a49',
            'protected' => 'color:#d73a49',
            'private' => 'color:#d73a49',
            'meta' => 'color:#b729d9',
            'key' => 'color:#032f62',
            'index' => 'color:#a71d5d',
        ]);
    }

    /**
     * Dump a variable with its source file and line number.
     */
    public function dumpWithSource(Data $data): void
    {
        if ($this->dumping) {
            $this->dump($data);

            return;
        }

        $this->dumping = true;

        $output = (string) $this->dump($data, true);

        $output = match (true) {
            str_contains($output, static::EXPANDED_SEPARATOR) => str_replace(
                static::EXPANDED_SEPARATOR,
                static::EXPANDED_SEPARATOR . $this->getDumpSourceContent(),
                $output,
            ),
            str_contains($output, static::NON_EXPANDED_SEPARATOR) => str_replace(
                static::NON_EXPANDED_SEPARATOR,
                $this->getDumpSourceContent() . static::NON_EXPANDED_SEPARATOR,
                $output,
            ),
            default => $output,
        };

        fwrite($this->outputStream, $output);

        $this->dumping = false;
    }

    /**
     * Get the source html content for the dump.
     */
    protected function getDumpSourceContent(): string
    {
        $dumpSource = $this->resolveDumpSource();

        if (is_null($dumpSource)) {
            return '';
        }

        ['file' => $file, 'line' => $line] = $dumpSource;

        $source = sprintf('%s%s', $file, is_null($line) ? '' : ":{$line}");

        return sprintf('<span style="color: %s;"> // %s</span>', static::$sourceOutputColor, $source);
    }

    /**
     * Resolve the source of the dump call.
     */
    public function resolveDumpSource(): array|null
    {
        if (static::$dumpSourceResolver === false) {
            return null;
        }

        if (static::$dumpSourceResolver) {
            return call_user_func(static::$dumpSourceResolver);
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        $sourceKey = null;

        foreach ($trace as $traceKey => $traceFile) {
            if (! isset($traceFile['file'])) {
                continue;
            }

            foreach (self::$adjustableTraces as $name => $key) {
                if (str_ends_with($traceFile['file'], str_replace('/', DIRECTORY_SEPARATOR, $name))) {
                    $sourceKey = $traceKey + $key;

                    break;
                }
            }

            if (! is_null($sourceKey)) {
                break;
            }
        }

        if (is_null($sourceKey)) {
            return null;
        }

        $file = $trace[$sourceKey]['file'] ?? null;
        $line = $trace[$sourceKey]['line'] ?? null;

        if (is_null($file) || is_null($line)) {
            return null;
        }

        return compact('file', 'line');
    }
}
