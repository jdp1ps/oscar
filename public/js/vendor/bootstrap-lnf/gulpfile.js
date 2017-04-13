/**
 * Created by jacksay on 21/05/15.
 * Task runner
 */

var gulp = require('gulp'),
    sass = require('gulp-sass'),
    webserver = require('gulp-webserver'),
    handlebars = require('gulp-compile-handlebars'),
    rename = require('gulp-rename'),
    // --- Configuration du serveur
    config = {
      server: {
        port: 4000
      }
    },
    directories = {
        src: {
          css: './src/assets/stylesheets/',
          hbs: './src/demo/'
        },
        dist: {
          css: './dist/css/',
          hbs: './dist/demo/'
        }
    };


// Default task
gulp.task('default', ['sass', 'hbs', 'watch:sass', 'watch:hbs', 'serve']);

////////////////////////////////////////////////////////////////////////////////
//
// SASS
//
////////////////////////////////////////////////////////////////////////////////
/**
 * Tâche permettant de compiler les fichiers SASS.
 *
 * Utilisation
 *
 * ```
 * $ gulp sass
 * ```
 */
gulp.task('sass', function () {
    gulp.src(directories.src.css + '**/*.scss')
        .pipe(sass())
        .pipe(gulp.dest(directories.dist.css));
});

gulp.task('watch:sass', function () {
    gulp.watch(directories.src.css + '**/*.scss', ['sass']);
});


gulp.task('hbs', function(){
  var datas = {
    colors: ['primary', 'secondary1', 'secondary2', 'complementary', 'success', 'info', 'danger', 'warning'],
    lights: ['ultralight', 'light', 'standard', 'hard', 'dark', 'grey'],
    sizes: ['xs', 'normal', 'big', 'hudge']
  };
  gulp.src(directories.src.hbs + 'demo.hbs')
    .pipe(handlebars(datas))
    .pipe(rename('demo.html'))
    .pipe(gulp.dest(directories.dist.hbs));
});

gulp.task('watch:hbs', function(){
  gulp.watch(directories.src.hbs + 'demo.hbs', ['hbs']);
});

////////////////////////////////////////////////////////////////////////////////
//
// SERVER
//
////////////////////////////////////////////////////////////////////////////////
gulp.task('serve', function () {
  gulp.src('dist')
    .pipe(webserver({
      fallback: 'demo/demo.html',
      port: 4000,
      livereload: true,
      directoryListing: false,
      open: true
    }));
});
