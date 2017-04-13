/**
 * Created by jacksay on 21/05/15.
 * Task runner
 */

var gulp = require('gulp'),
    debug = require('gulp-debug'),
    sass = require('gulp-sass'),
    changed = require('gulp-changed'),
    cached = require('gulp-cached'),
    babel = require('gulp-babel'),
    plumber = require('gulp-plumber'),
    umd = require('gulp-umd'),
    ext_replace = require('gulp-ext-replace'),

// Paths & locations
    directories = {
        css: './public/css/',
        jsComponents: './public/js/components/',
        jsModels: './public/js/models/'
    };


// Basic example
gulp.task('default', ['sass', 'watch:sass', 'oscar-components', 'oscar-model']);


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
    gulp.src(directories.css + '**/*.scss')
        .pipe(cached('sass'))
        .pipe(sass())
        .pipe(gulp.dest(directories.css));
});

gulp.task('oscar-components', function(){
    gulp.src(directories.jsComponents +'src/*.js')
        .pipe(plumber())
        .pipe(babel())
        .pipe(gulp.dest(directories.jsComponents+'build'))
})

gulp.task('oscar-model', function(){
    gulp.src(directories.jsModels +'src/*.js')
        .pipe(plumber())
        .pipe(babel({presets: ['es2015']}))
        .pipe(umd())
        .pipe(gulp.dest(directories.jsModels+'build'))
})

gulp.task('watch:sass', function () {
    gulp.watch(directories.css + '**/*.scss', ['sass']);
    gulp.watch(directories.jsComponents + 'src/*.js', ['oscar-components']);
    gulp.watch(directories.jsModels + 'src/*.js', ['oscar-model']);
});