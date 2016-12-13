<?php

// start the autoloader
load([
  'kirby\\patterns\\pattern' => __DIR__ . DS . 'lib' . DS . 'pattern.php',
  'kirby\\patterns\\lab'     => __DIR__ . DS . 'lib' . DS . 'lab.php'
]);

// load all helper functions
require_once(__DIR__ . DS . 'lib' . DS . 'helpers.php');

// load html beautifier
require_once(__DIR__ . DS . 'vendor' . DS . 'htmlawed' . DS . 'htmlawed.php');

// start the lab
new Kirby\Patterns\Lab();