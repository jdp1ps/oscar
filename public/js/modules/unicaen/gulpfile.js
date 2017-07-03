var gulp    = require('gulp'),
    debug   = require('gulp-debug'),
    babel   = require('gulp-babel'),
    umd     = require('gulp-umd'),
    plumber = require('gulp-plumber'),
    mocha   = require('gulp-mocha'),
    gutil   = require('gulp-util'),
    minify  = require('gulp-minify'),
    sass    = require('gulp-sass')
;

// Basic example
gulp.task('default', ['examples-copy', 'js', 'watch:js', 'sass']);

// Compilation sass
gulp.task('sass', function () {
  return gulp.src('src/css/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('dist/css'));
});

// Lancement des tests unitaires
gulp.task('mocha', ['js'], function() {
    return gulp.src(['test/*.js'], { read: false })
        .pipe(plumber())
        .pipe(mocha({ reporter: 'list' }))
        .on('error', gutil.log);
});

gulp.task('examples-copy', function(){
  gulp.src(['src/**/*.html'])
    .pipe(gulp.dest('dist'));
})

gulp.task('prod', ['js'], function(){
  gulp.src(['dist/**/*.js', '!dist/*-min.js'])
      .pipe(minify({
          ext: {
            min:'-min.js'
          }
      }))
      .pipe(gulp.dest('dist'))
})

// Compilation du Javascript
gulp.task('js', function(){
    gulp.src(['src/js/Datepicker.js', 'src/js/EventDT.js'])
        .pipe(plumber())
        .pipe(babel())
        .pipe(umd({
          dependencies: function (file) {
            return [{
              name: 'moment',
              amd: 'moment',
              cjs: 'moment',
              global: 'moment',
              param: 'moment'
            }]
          }
        }))
        .pipe(gulp.dest('dist/js'))

    gulp.src(['src/js/ICalAnalyser.js'])
        .pipe(plumber())
        .pipe(babel())
        .pipe(umd({
            dependencies: function (file) {
                return [
                    {
                        name: 'moment',
                        amd: 'moment',
                        cjs: 'moment',
                        global: 'moment',
                        param: 'moment'
                    },
                    {
                        name: 'ical',
                        amd: 'ical',
                        cjs: 'ical.js',
                        global: 'ical',
                        param: 'ical'
                    }
                ]
            }
        }))
        .pipe(gulp.dest('dist/js'))

    gulp.src(['src/js/calendar.js'])
        .pipe(plumber())
        .pipe(babel())
        .pipe(umd({
            dependencies: function (file) {
                return [
                    {
                        name: 'moment',
                        amd: 'moment',
                        cjs: 'moment',
                        global: 'moment',
                        param: 'moment'
                    },
                    {
                        name: 'ICalAnalyser',
                        amd: 'ICalAnalyser',
                        cjs: 'ICalAnalyser',
                        global: 'ICalAnalyser',
                        param: 'ICalAnalyser'
                    },
                    {
                        name: 'EventDT',
                        amd: 'EventDT',
                        cjs: 'EventDT',
                        global: 'EventDT',
                        param: 'EventDT'
                    },
                    {
                        name: 'Datepicker',
                        amd: 'Datepicker',
                        cjs: 'Datepicker',
                        global: 'Datepicker',
                        param: 'Datepicker'
                    },
                    {
                        name: 'bootbox',
                        amd: 'bootbox',
                        cjs: 'bootbox',
                        global: 'bootbox',
                        param: 'bootbox'
                    }
                    ,
                    {
                        name: 'Papa',
                        amd: 'papa-parse',
                        cjs: 'papa-parse',
                        global: 'Papa',
                        param: 'Papa'
                    }
                ]
            }
        }))
        .pipe(gulp.dest('dist/js'))
});

gulp.task('watch:js', ['js', 'mocha'], function () {
    gulp.watch(['src/**/*.js', 'test/*.js'], ['mocha']);
    gulp.watch(['src/css/**/*.scss'], ['sass']);
    gulp.watch(['src/**/*.html'], ['examples-copy']);
});
