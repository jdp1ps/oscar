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

var reportOptions = {
    err: true, // default = true, false means don't write err
    stderr: true, // default = true, false means don't write stderr
    stdout: true // default = true, false means don't write stdout
};

gulp.task('module-oscar-milestones', function(cb){
    exec('poi build --format umd --moduleName milestones public/js/oscar/src/Milestones.vue --filename.css milestones.css --filename.js Milestones.js --dist public/js/oscar/dist',
        function (err, stdout, stderr) {
            console.log("STDOUT : ", stdout);
            console.log("STDERR : ", stderr);
            cb(err);
        }
    );
});

gulp.task('module-oscar-timesheet', function(cb){
    exec('poi build --format umd --moduleName timesheetleader public/js/oscar/src/TimesheetLeader.vue --filename.css timesheetleader.css --filename.js TimesheetLeader.js --dist public/js/oscar/dist',
        function (err, stdout, stderr) {
            console.log("STDOUT : ", stdout);
            console.log("STDERR : ", stderr);
            cb(err);
        });
});

gulp.task('module-oscar-payments', function(cb){
    exec('poi build --format umd --moduleName payments public/js/oscar/src/Payments.vue --filename.css payments.css --filename.js Payments.js --dist public/js/oscar/dist',
        function (err, stdout, stderr) {
            console.log("STDOUT : ", stdout);
            console.log("STDERR : ", stderr);
            cb(err);
        });
});

gulp.task('module-oscar-datepicker', function(cb){
    exec('poi build --format umd --moduleName datepicker public/js/oscar/src/Datepicker.vue --filename.css datepicker.css --filename.js Datepicker.js --dist public/js/oscar/dist',
        function (err, stdout, stderr) {
            console.log("STDOUT : ", stdout);
            console.log("STDERR : ", stderr);
            cb(err);
        });
});

gulp.task('module-oscar-authentification', function(cb){
    exec('poi build --format umd --moduleName authentification public/js/oscar/src/Authentification.vue --filename.css authentification.css --filename.js Authentification.js --dist public/js/oscar/dist',
        function (err, stdout, stderr) {
            console.log("STDOUT : ", stdout);
            console.log("STDERR : ", stderr);
            cb(err);
        });
});

gulp.task('module-oscar-activityclone', function(cb){
    exec('poi build --format umd --moduleName activityclone public/js/oscar/src/Activityclone.vue --filename.css activityclone.css --filename.js Activityclone.js --dist public/js/oscar/dist',
        function (err, stdout, stderr) {
            console.log("STDOUT : ", stdout);
            console.log("STDERR : ", stderr);
            cb(err);
        });
});

gulp.task('module-oscar-notification', function(cb){
    exec('poi build --format umd --moduleName notification public/js/oscar/src/Notification.vue --filename.css notification.css --filename.js Notification.js --dist public/js/oscar/dist',
        function (err, stdout, stderr) {
            console.log("STDOUT : ", stdout);
            console.log("STDERR : ", stderr);
            cb(err);
        });
});

gulp.task('oscar-build', ['module-oscar-timesheet', 'module-oscar-milestones', 'module-oscar-payments', 'module-oscar-authentification', 'module-oscar-payments', 'module-oscar-milestones', 'module-oscar-activityclone']);

gulp.task('watch-oscar', ['module-oscar-payments','module-oscar-timesheet','module-oscar-milestones'], function(){
    gulp.watch(['./public/js/oscar/src/Timesheet*'], ['module-oscar-timesheet']);
    gulp.watch(['./public/js/oscar/src/Milestone*'], ['module-oscar-milestones']);
    gulp.watch(['./public/js/oscar/src/Payment*'], ['module-oscar-payments']);
    gulp.watch(['./public/js/oscar/src/Authentification*'], ['module-oscar-authentification']);
    gulp.watch(['./public/js/oscar/src/Datepicker*'], ['module-oscar-payments', 'module-oscar-milestones']);
});



gulp.task('watch', function () {
    gulp.watch(directories.css + '**/*.scss', ['sass']);
    gulp.watch(directories.jsComponents + 'src/*.js', ['oscar-components']);
    gulp.watch(directories.jsModels + 'src/*.js', ['oscar-model']);
    gulp.watch('./public/js/modules/unicaen/src/css/*.scss', ['modules-css']);
    gulp.watch('./public/js/modules/unicaen/src/js/*.js', ['modules-js']);


});


