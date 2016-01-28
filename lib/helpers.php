<?php

function pattern($path, $data = [], $return = false) {
  if($return === true) {
    return new Kirby\Patterns\Pattern($path, $data);
  } else {
    echo new Kirby\Patterns\Pattern($path, $data);     
  }
}