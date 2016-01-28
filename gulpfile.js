var gulp   = require('gulp');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var cssmin = require('gulp-cssmin');

gulp.task('js', function() {

  return gulp.src([
      'assets/src/js/prism.js',
      'assets/src/js/index.js'
    ]) 
    .pipe(concat('index.js')) 
    .pipe(gulp.dest('assets/dist'))
    .pipe(rename('index.min.js'))
    .pipe(uglify()) 
    .pipe(gulp.dest('assets/dist'));

});

gulp.task('css', function() {

  return gulp.src([
      'assets/src/css/prism.css',
      'assets/src/css/index.css',
    ])  
    .pipe(concat('index.css')) 
    .pipe(gulp.dest('assets/dist'))
    .pipe(rename('index.min.css'))
    .pipe(cssmin()) 
    .pipe(gulp.dest('assets/dist'));    

});

gulp.task('watch', function() {
  gulp.watch('assets/src/**/*.css', ['css']);
  gulp.watch('assets/src/**/*.js', ['js']);
});

gulp.task('default', [
  'css', 
  'js'
]);