<?php

namespace Kirby\Patterns;

use A;
use Collection;
use Dir;
use Exception;
use Media;
use Str;
use Tpl;

class Pattern {

  public $lab;
  public $name;
  public $path;
  public $data;
  public $root;
  public $config = null;

  public function __construct($path = '', $data = []) {
    $this->lab  = lab::instance();
    $this->name = basename($path);
    $this->path = trim($path, '/');
    $this->root = $this->lab->root() . DS . $path;
    $this->data = $data;
  }

  public function file($ext) {
    return $this->root . DS . $this->name . '.' . $ext;
  }

  public function defaults() {
    return (array)a::get($this->config(), 'defaults', []);
  }

  public function url() {
    return $this->lab->url() . '/' . $this->path;
  }

  public function isHidden() {
    return a::get($this->config(), 'hide', false);
  }

  public function data() {

    $data     = $this->data;
    $defaults = $this->defaults();

    if(lab::$mode == 'preview') {
      $callback    = a::get($this->config(), 'preview');
      $previewData = (array)call($callback);
      $defaults    = array_merge($defaults, $previewData);
    }

    foreach($defaults as $key => $value) {
      if(!isset($this->data[$key]) and !isset(tpl::$data[$key])) {
        if(is_a($value, 'Closure')) {
          $data[$key] = call($value, [$this]);
        } else {
          $data[$key] = $value;
        }
      } else if(isset($this->data[$key])) {
        $data[$key] = $this->data[$key];
      } else {
        $data[$key] = tpl::$data[$key];
      }
    }

    return $data;

  }

  public function template() {
    return tpl::load($this->file('html.php'), $this->data());
  }

  public function preview() {

    $file = $this->file('preview.php');

    if (file_exists($file)) {
      return tpl::load($file, $this->data());
    }

    return $this->render();

  }

  public function render() {
    return $this->template();
  }

  public function path() {
    return $this->path;
  }

  public function name() {
    return $this->name;
  }

  public function title() {
    return a::get($this->config(), 'title', $this->name());
  }

  public function files() {

    $files = new Collection;

    foreach(dir::read($this->root) as $file) {
      if(is_dir($this->root . DS . $file)) continue;
      $url = $this->url() . '/' . $file . '?raw=true';
      $media = new Media($this->root . DS . $file, $url);
      $files->append($media->filename(), $media);
    }

    return $files;

  }

  public function isOpen($path = null) {

    if (is_null($path) && ($pattern = $this->lab->current())) {
      $path = $pattern->path();
    }

    return ( $path == $this->path ) || str::startsWith($path, $this->path);

  }

  public function children() {

    $children = new Collection;

    foreach(dir::read($this->root) as $dir) {
      if(!is_dir($this->root . DS . $dir)) continue;
      $pattern = new Pattern($this->path . '/' . $dir);
      $children->append($pattern->path(), $pattern);
    }

    return $children;

  }

  public function config() {

    if(!is_null($this->config)) return $this->config;

    $config = $this->file('config.php');

    if(file_exists($config)) {
      return $this->config = (array)require($config);
    } else {
      return $this->config = [];
    }

  }

  public function exists() {
    return is_dir($this->root);
  }

  public function __toString() {
    try {
      return (string)$this->render();
    } catch(Exception $e) {
      return '';
    }
  }

}
