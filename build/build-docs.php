#! /bin/env php
<?php

// Load readme.md template
$readme = file_get_contents(__DIR__ . '/README.template.md');

// Creating simple tags
$readme = fillReadme($readme, 'creating-simple-tags');

file_put_contents(__DIR__ . '/../README.md', $readme);

function fillReadme(&$file, $example)
{
    $process = proc_open(
        'php '.__DIR__.'/examples/' . $example . '.php',
        array(
            array("pipe","r"),
            array("pipe","w"),
            array("pipe","w")
        ),
        $pipes
    );
    $phpOutput = stream_get_contents($pipes[1]);
    return str_replace(
        '{{'.$example.'}}',
        '`````php'.
        PHP_EOL.
        file_get_contents(__DIR__ . '/examples/' . $example . '.php').
        '`````'.
        PHP_EOL.
        '`````jade'.
        PHP_EOL.
        file_get_contents(__DIR__ . '/examples/' . $example . '.jade').
        '`````'.
        PHP_EOL.
        '`````html'.
        PHP_EOL.
        $phpOutput.
        PHP_EOL.
        '`````'.
        PHP_EOL,
        $file
    );
}
