<?php

require __DIR__ . '/../../vendor/autoload.php';

use Jade\Jade;

$jade = new Jade();
echo $jade->render(__DIR__ .'/'. basename(__FILE__, '.php').'.jade');
