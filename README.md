# Jade.php [![Build Status](https://travis-ci.org/opendena/jade.php.png?branch=master)](https://travis-ci.org/opendena/jade.php)

Jade.php adds inline PHP scripting support to the [Jade](http://jade-lang.com) template compiler.
## Installation
We strongly recommand to use composer
`````javascript
{
    "require":{
        "opendena/jade.php":"dev-master"
    }
}
`````

## Use

### Creating simple tags
`````php
<?php

require __DIR__ . '/../../vendor/autoload.php';

use Jade\Jade;

$jade = new Jade();
echo $jade->render(__DIR__ .'/'. basename(__FILE__, '.php').'.jade');
`````
`````jade
div
  address
  i
  strong
`````
`````html
<div><address></address><i></i><strong></strong></div>
`````


## Public API
`````php
$jade = new Jade\Jade();

// Parse a template (supports both string inputs and files)
echo $jade->render('h1 it works!');
`````

## Syntax

See the [offical documentation](https://github.com/visionmedia/jade#readme)
Somme features are missing (tests are comment)

Open an issue if you find something missing.
