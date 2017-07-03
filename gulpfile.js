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
    exec = require('child_process').exec,

// Paths & locations
    directories = {
        css: './public/css/',
        jsComponents: './public/js/components/',
        jsModels: './public/js/models/'
    },

    /**
     * Retourne la dépendance pour la production de la configuration AMD.
     *
     * @param name
     * @returns {{name: *, amd: *, cjs: *, global: *, param: *}}
     */
    dependency = function(name, alias){
        if( alias == undefined ){
            alias = name;
        }
        return {
            'name': alias,
            'amd': name,
            'cjs': name,
            'global': alias,
            'param': name
        };
    },

    /**
     *
     * @param names
     * @returns {Array}
     */
    dependencies = function( names ){
        var dependencies = [];
        names.forEach(function(d){
            dependencies.push(dependency(d));
        });
        return dependencies;
    }
;





// Basic example
gulp.task('default', ['sass', 'watch:sass', 'oscar-components', 'oscar-model', 'modules-js', 'modules-css']);



gulp.task('modules-js', function(){
    exec('gulp js --gulpfile ./public/js/modules/unicaen/gulpfile.js');
});

gulp.task('modules-css', function(){
    exec('gulp sass --gulpfile ./public/js/modules/unicaen/gulpfile.js');
});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// MODULES
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
    gulp.src([
        directories.jsComponents +'src/*.js',
        '!' + directories.jsComponents +'src/Datepicker.js',
        '!' +directories.jsComponents +'src/EventDT.js',
        '!' +directories.jsComponents +'src/ICalAnalyser.js',
        '!' +directories.jsComponents +'src/calendar.js'
    ])
        .pipe(plumber())
        .pipe(babel({
            "plugins": ["transform-es2015-modules-amd"]
        }))
        .pipe(gulp.dest(directories.jsComponents+'build'));

    gulp.src([directories.jsComponents +'src/Datepicker.js', directories.jsComponents +'src/EventDT.js'])
        .pipe(plumber())
        .pipe(babel())
        .pipe(umd({
            dependencies: function (file) {
                return dependencies(['moment']);
            }
        }))
        .pipe(gulp.dest(directories.jsComponents+'build'))

    gulp.src([directories.jsComponents +'src/ICalAnalyser.js'])
        .pipe(plumber())
        .pipe(babel())
        .pipe(umd({
            dependencies: function (file) {
                return dependencies(['moment', 'ical']);
            }
        }))
        .pipe(gulp.dest(directories.jsComponents+'build'))

    gulp.src(directories.jsComponents +'src/calendar.js')
        .pipe(plumber())
        .pipe(babel())
        .pipe(umd({
            dependencies: function (file) {
                return dependencies(['moment', 'IcalAnalyser', 'EventDT']);
            }
        }))
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
    gulp.watch('./public/js/modules/unicaen/src/css/*.scss', ['modules-css']);
    gulp.watch('./public/js/modules/unicaen/src/js/*.js', ['modules-js']);
});