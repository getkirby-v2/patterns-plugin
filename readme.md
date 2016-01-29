# Kirby Patterns

With Kirby Patterns you can build your site with clean reusable modules, while the plugin creates a living styleguide for you automatically. 

## Video Demo

<https://vimeo.com/153132557>

## Screenshots

<https://gist.github.com/bastianallgeier/27f604fc838a266be482#gistcomment-1677662>

## Installation

0. Install Kirby. I'd recommend [Kirby's Plainkit](https://github.com/getkirby/plainkit)
1. [Download Kirby Patterns](https://github.com/getkirby/patterns/archive/master.zip) from Github
2. Copy the patterns folder into `/site/plugins` (Create the plugins folder if it does not exist yet)
3. Create a new `/site/patterns` folder and start building your patterns in there.

## Patterns readme.md

As on of your first steps after the installation, you should put a `readme.md` in `/site/patterns` This will automatically be used by the Patterns interface to provide a nice little introduction page for your library.

## Options

The following options can be set in your `/site/config/config.php`

```php
c::set('patterns.title', 'Patterns');
c::set('patterns.path', 'patterns');
c::set('patterns.directory', '/var/www/yoursite.com/site/patterns');
c::set('patterns.lock', false);
c::set('patterns.preview.css', 'assets/css/index.css');
c::set('patterns.preview.js', 'assets/js/index.js');
c::set('patterns.preview.background', false);
```

### patterns.title

Sets the title, which appears in the browser and in the topbar of the Patterns interface. By default this is set to `Patterns`

### patterns.path

You can use this option to change the location of the Patterns interface. By default it will be located at `http://yourdomain.com/patterns` Only set the path with this option though. The URL must be omitted.

### patterns.directory

Set the full path to your patterns directory with this option. By default the patterns directory must be located in `site/patterns`

### patterns.lock

You can lock the Patterns interface, so it will only be accessible by users, who logged into the Kirby Panel first.

### patterns.preview.css 

Use this option to set where the final CSS for your patterns is located. All the specified CSS files will be loaded in the preview screen in order to style your patterns appropriately. By default the Patterns interface is looking for a `/assets/css/index.css` file.

You can load multiple CSS files by passing an array of files: 

```php
c::set('patterns.preview.css', ['assets/css/main.css', 'assets/css/theme.css']);
```

### patterns.preview.js

Use this option to set where the final JS for your patterns is located. All the specified JS files will be loaded in the preview screen (in the footer) in order to apply behaviour to your patterns. By default the Patterns interface is looking for a `/assets/js/index.js` file.

You can load multiple JS files by passing an array of files: 

```php
c::set('patterns.preview.js', ['assets/js/jquery.js', 'assets/js/patterns.js']);
```

### patterns.preview.background

You can use this option to set the default background color for the pattern preview screen. Any valid CSS value can be used. By default no specific color value is being set. A pattern can overwrite this with the `background` option (see further down)


## Creating a pattern

You can add a subfolder to `/site/patterns` for each of your patterns and even nest them however you like. 

A pattern folder can contain any number of files, which belong to the pattern. 

You should provide a markdown file in order to add documentation for your pattern. The Pattern interface will automatically parse the markdown and convert it into a nice doc page. You can even use Kirbytext in your markdown files. 



## Pattern template

Patterns don't necessarily need to have a template file, but if you want to use them in your templates or snippets, you should create a file called `{patternname}.html.php` in the pattern folder. 

In your pattern template you can use all the methods and functions from Kirby's APIs. You have full access to the `$site`, `$pages` and `$page` variable and everything else mentioned in the cheat sheet. 

## Pattern configuration

You can add an optional `{patternname}.config.php` file to your pattern to further set it up.

A pattern config file must return an associative PHP array: 

```php
<?php 

return [
  // your pattern config goes here
];
```

### defaults

With the `defaults` variable you can pass data to the pattern, which will be used if it is not being overwritten by the pattern method, when using the pattern.

```php
return [
  'defaults' => [
    'title' => 'Default title',
    'text'  => 'Default text'
  ]
];
```

### background

With the `background` variable you can determine the background color for the pattern preview. This can be helpful if a pattern does not define a background color in CSS itself, but is always placed on a dark background for example, when being used within another pattern. Any valid CSS color value can be used.

```php
return [
  'background' => '#000'
];
```

### hide

It might be useful sometimes to create a pattern in your patterns folder, which should not be visible in the Patterns interface. You can use the `hide` variable therefor to hide particular patterns.

```php
return [
  'hide' => true
];
```

### preview 

The preview callback option can be used to execute code before the pattern is being rendered in the Patterns interface. This can be helpful to generate dynamic defaults for the pattern, which will only be used for the preview and not when the pattern is used on your site. 

Optionally the callback can return an associative array, which will be used to set/overwrite the defaults for the pattern.

```php 
return [
  'preview' => function() {

    site()->visit('some/page');

    return [
      'title' => page('blog/article-xyz')->title(),
      'text'  => 'Lorem ipsum…'
    ]

  }
];
```


## Using a pattern 

To use a pattern in your Kirby templates or snippets you can use the new `pattern()` method, which will be available as soon as the plugin is being installed. 

```php
<? pattern('header/logo') ?>
```

### Passing options to the pattern

You can pass additional variables and options to the pattern and overwrite its defaults that way. 

```php
<? pattern('header/logo', ['class' 'logo logo-on-black']) ?>
```

## Nesting patterns

You can use the `pattern()` method directly within a pattern template file to nest patterns. 


## Build process example

Since all the CSS and JS files should be stored directly with a pattern, you probably need some kind of build process in order to pre/post-process, concatenate and compress them. The plugin does not force you into any specific workflow. Here's a simple example gulp file, which I am using as a boilerplate for my projects though: 

```js
var gulp   = require('gulp');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var cssmin = require('gulp-cssmin');
var sass   = require('gulp-sass');
var image  = require('gulp-image');

gulp.task('js', function() {

  return gulp.src([
      // array of js files. i.e.:
      // 'site/patterns/js/jquery/jquery.js',
      // 'site/patterns/gallery/fotorama/fotorama.js'
    ]) 
    .pipe(concat('index.js')) 
    .pipe(gulp.dest('assets/js'))
    .pipe(rename('index.min.js'))
    .pipe(uglify()) 
    .pipe(gulp.dest('assets/js'));

});

gulp.task('css', function() {

  return gulp.src('site/patterns/site/site.scss')
    .pipe(sass().on('error', sass.logError)) 
    .pipe(rename('index.css'))
    .pipe(gulp.dest('assets/css'))
    .pipe(rename('index.min.css'))
    .pipe(cssmin()) 
    .pipe(gulp.dest('assets/css'));    

});

gulp.task('images', function() {
  gulp.src('site/patterns/**/*.{jpg,gif,png,svg}')
    .pipe(image())
    .pipe(gulp.dest('assets/images'));
});

gulp.task('default', [
  'css', 
  'js', 
  'images'
]);

gulp.task('watch', ['default'], function() {
  gulp.watch('site/patterns/**/*.scss', ['css']);
  gulp.watch('site/patterns/**/*.js', ['js']);
  gulp.watch('site/patterns/**/*.{jpg,gif,png,svg}', ['images']);
});
```

In order to use this, you must install the dependencies via npm first: 

```
npm install --save-dev gulp
npm install --save-dev gulp-concat
npm install --save-dev gulp-rename
npm install --save-dev gulp-uglify
npm install --save-dev gulp-cssmin
npm install --save-dev gulp-sass
npm install --save-dev gulp-image
```

Afterwards you can run `gulp` in order to build the assets or `gulp watch` to work on your patterns and convert the assets on the fly.

I am by no means the best frontend dev out there, so I'm sure you will find a smarter way to setup your build process with grunt, gulp or npm anyway. 

## Customizing the design of the Patterns interface

The dark default theme might not be for everyone, but it's very easy to load your own stylesheet and even js to customize the interface. The app is looking for an `/assets/patterns/index.css` and a `/assets/patterns/index.js` file. If not provided it will load the default theme. So by adding those files you can overwrite any styles you want. 

## Requirements

- Kirby 2.2.3+
- PHP 5.4+

## License 

<http://www.opensource.org/licenses/mit-license.php>

## Author

Bastian Allgeier   
<bastian@getkirby.com>  
<http://getkirby.com>  
<http://twitter.com/getkirby>
