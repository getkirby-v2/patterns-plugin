<?php

namespace Kirby\Patterns;

use A;
use Exception;
use F;
use HTML;
use Obj;
use Response;
use Router;
use Tpl;

class Lab {

  static $instance = null;
  static $mode;

  public $path;
  public $title;
  public $root;

  public function __construct() {

    $this->kirby = kirby();
    $this->path  = $this->kirby->option('patterns.path', 'patterns');
    $this->title = $this->kirby->option('patterns.title', 'Patterns');
    $this->root  = $this->kirby->option('patterns.directory', $this->kirby->roots()->site() . DS . 'patterns');

    $lab = $this;

    // inject the patterns routes
    kirby()->routes([
      [
        'pattern' => $this->path . '/(:all?)', 
        'action' => function($path = null) use($lab) {
          return $lab->run($path);
        }
      ]
    ]);

    static::$instance = $this;

  }

  public static function instance() {
    return static::$instance = is_null(static::$instance) ? new static : static::$instance;
  }

  public function view($name, $data = []) {
    return tpl::load(dirname(__DIR__) . DS . 'views' . DS . $name . '.php', $data);
  }

  public function path() {
    return $this->path;
  }

  public function title() {
    return $this->title;
  }

  public function root() {
    return $this->root;
  }

  public function url() {
    return url($this->path());
  }

  public function run($path = '/') {

    if($this->kirby->option('patterns.lock') && !$this->kirby->site()->user()) {
      go($this->kirby->option('error'));
    }

    // error handling
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(function($e) {
      throw $e;
      return \Whoops\Handler\Handler::QUIT;
    });

    $whoops->register();

    tpl::$data = [
      'site'  => $this->kirby->site(),
      'pages' => $this->kirby->site()->children(),
      'page'  => $this->kirby->site()->find($this->kirby->option('home')),
      'lab'   => $this
    ];

    $router = new Router();
    $router->register([
      [
        'pattern' => '/', 
        'action'  => function() {

          $readme = $this->root() . DS . 'readme.md';

          if(!is_dir($this->root())) {
            $modal = $this->view('modals/folder');
          } else {
            $modal = null;
          }
        
          if(is_file($readme)) {
            $markdown = kirbytext(f::read($readme));
          } else {
            $markdown = null;            
          }

          return $this->view('layouts/main', [
            'title'   => $this->title(),
            'menu'    => $this->menu(),
            'content' => $this->view('views/dashboard', ['markdown' => $markdown]),
            'modal'   => $modal
          ]);

        }
      ],
      [
        'pattern' => 'assets/(:any)', 
        'action'  => function($file) {

          switch($file) {
            case 'index.js':
            case 'index.min.js':
              $mime = 'text/javascript';
              break;
            case 'index.css':
            case 'index.min.css':
              $mime = 'text/css';
              break;
            default:
              return new Response('Not found', 'text/html', 404); 
              break;
          }

          // build the root for the file
          $file = dirname(__DIR__) . DS . 'assets/dist/' . $file;
          return new Response(f::read($file), $mime);

        }
      ],
      [
        'pattern' => '(:all)/preview',
        'action'  => function($path) {

          lab::$mode = 'preview';

          $pattern = new Pattern($path);
          $config  = $pattern->config();
          
          try {
            $html = $pattern->render();
          } catch(Exception $e) {
            $html = '';
          }

          return $this->view('views/preview', [
            'pattern'    => $pattern,
            'html'       => $html,
            'background' => a::get($config, 'background', $this->kirby->option('patterns.preview.background')),
            'css'        => $this->kirby->option('patterns.preview.css', 'assets/css/index.css'),
            'js'         => $this->kirby->option('patterns.preview.js', 'assets/js/index.js')
          ]);

        }
      ],
      [
        'pattern' => '(:all)', 
        'action'  => function($path) {

          $pattern = new Pattern($path);
          $file    = null;

          if(!$pattern->exists()) {
            
            $filename = basename($path);
            $path     = dirname($path);

            if($path == '.') {
              $preview = $this->view('previews/error', [
                'error' => 'The pattern could not be found'
              ]);
            } else {

              $pattern = new Pattern($path);
              $file    = $pattern->files()->get($filename);

              if($file) {
                $preview = $this->preview($pattern, $file);       
              } else {
                $preview = $this->view('previews/error', [
                  'error' => 'The file could not be found'
                ]);
              }

            }

          } else if($file = $pattern->files()->get($pattern->name() . '.html.php')) {
            go($pattern->url() . '/' . $file->filename());
          } else if($file = $pattern->files()->first()) {
            go($pattern->url() . '/' . $file->filename());
          } else {
            $preview = $this->view('previews/empty');
          }
          
          if($pattern->isHidden()) {
            go($this->url());
          }

          return $this->view('layouts/main', [
            'title'   => $this->title() . ' / ' . $pattern->title(),
            'menu'    => $this->menu(null, $path),
            'content' => $this->view('views/pattern', [
              'preview' => $preview,
              'info'    => $this->view('snippets/info', [
                'pattern' => $pattern, 
                'file'    => $file,
              ])
            ])
          ]);

        }
      ]
    ]);

    if($route = $router->run($path ? $path : '/')) {
      return new Response(call($route->action(), $route->arguments()));
    } else {
      go('error');
    }

  }

  public function menu($patterns = null, $path = '') {
  
    if(is_null($patterns)) {
      $pattern  = new Pattern();
      $patterns = $pattern->children();
    }

    if(!$patterns->count()) return null;

    $html = ['<ul class="nav">'];

    foreach($patterns as $pattern) {

      if($pattern->isHidden()) continue;

      $html[] = '<li>';
      $html[] = html::a($pattern->url(), '<span>' . $pattern->title() . '</span>', ['class' => $path == $pattern->path() ? 'active' : null]);

      if($pattern->isOpen($path)) {
        $html[] = $this->menu($pattern->children(), $path);        
      }

      $html[] = '</li>';

    }

    $html[] = '</ul>';

    return implode(array_filter($html));

  }

  public function preview($pattern, $file) {

    $data = [
      'pattern' => $pattern,
      'file'    => $file,
    ];

    if(get('raw') == 'true') {
      $this->raw($pattern, $file);
    }

    if($file->filename() == $pattern->name() . '.html.php') {

      $views   = ['preview', 'html', 'php'];      
      $snippet = 'html';

      // pass the mode to the template
      $data['view'] = in_array(get('view'), $views) ? get('view') : $this->kirby->option('patterns.preview.mode', 'preview');

      switch($data['view']) {
        case 'preview':

          try {
            lab::$mode = 'preview';
            $pattern->render();
            $data['content'] = '<iframe src="' . $pattern->url() . '/preview"></iframe>';
          } catch(Exception $e) {
            $data['content'] = $this->error($e);
          }

          break;
        case 'php':
          $data['content'] = $this->codeblock($file);
          break;
        case 'html':          
          $data['content'] = $this->codeblock($pattern);
          break;
      }

    } else if(in_array(strtolower($file->extension()), ['gif', 'jpg', 'jpeg', 'svg', 'png'])) {

      $snippet = 'image';

    } else if(in_array(strtolower($file->extension()), ['md', 'mdown'])) {

      $snippet = 'markdown';
      $data['content'] = kirbytext($file->read());

    } else {

      $ext  = $file->extension();
      $code = ['php', 'html', 'js', 'css', 'scss', 'less', 'json', 'txt'];

      if(in_array($ext, $code)) {
        $snippet = 'code';
        $data['content'] = $this->codeblock($file);
      } else {
        $snippet = 'empty';
      }

    }

    return $this->view('previews/' . $snippet, $data);

  }

  public function raw($pattern, $file) {
    $file->show();
  }

  public function codeblock($object, $lang = 'markup') {

    $langs = [
      'css'   => 'css',
      'php'   => 'php',
      'js'    => 'js',
      'scss'  => 'sass',
      'md'    => 'markdown',
      'mdown' => 'markdown',      
    ];

    try {

      if(is_a($object, 'Media')) {
        $code = $object->read();
        $lang = a::get($langs, $object->extension(), 'markup');
      } else if(is_a($object, 'Kirby\\Patterns\\Pattern')) {
        $code = htmlawed($object->render(), ['tidy' => 1]);        
        $lang = 'php';
      } else if(is_string($object)) {
        $code = $object;
      } else {
        $code = '';
      }

    } catch(Exception $e) {
      return $this->error($e);
    }

    if(strlen($code) > 20000) {
      $lang = 'none';
    }

    return '<pre><code class="language-' . $lang . '">' . htmlspecialchars(trim($code)) . '</code></pre>';

  }

  public function error($e) {
    return '<div class="error">There\'s an error in your pattern: <strong>' . $e->getMessage() . '</strong></div>';    
  }

  public function theme() {

    $assets = $this->kirby->roots()->index() . DS . 'assets' . DS . 'patterns';

    $theme = new Obj;
    $theme->css = file_exists($assets . DS . 'index.css') ? 'assets/patterns/index.css' : $this->url() . '/assets/index.min.css';
    $theme->js  = file_exists($assets . DS . 'index.js')  ? 'assets/patterns/index.js'  : $this->url() . '/assets/index.min.js';

    return $theme;

  }

}