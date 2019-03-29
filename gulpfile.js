const { watch, src, dest } = require('gulp');

const
    sass = require('gulp-sass'),
    directories = {
        css: './public/css/'
    };

function defaultTask(cb) {
    watchFiles();
}

// USE : node node_modules/.bin/gulp sass
function sassTask() {
    return src(directories.css + '*.scss')
        .pipe(sass())
        .pipe(dest(directories.css));
}

// USE : node node_modules/.bin/gulp watch
function watchFiles(){
    watch(directories.css +'*.scss', { events: 'all' }, sassTask);
}

exports.sass = sassTask;
exports.watch = watchFiles;
exports.default = defaultTask;