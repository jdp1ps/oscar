const { watch, src, dest } = require('gulp');
const exec = require('child_process').exec;

const pathModuleSrc = './src/';
const pathModuleDest = './../public/js/oscar/dist/'

function defaultTask(cb) {
    console.log("No default task");
    cb();
}

function compile(module){
    let moduleSrc = pathModuleSrc + module + ".vue";
    let moduleDest = pathModuleDest;
    console.log("Compile ", module, " from ", moduleSrc, " to ", moduleDest);
    let cmd = "node node_modules/.bin/vue-cli-service build --name "+module+" --dest " + moduleDest + " --no-clean --formats umd,umd-min --target lib " + moduleSrc;
    console.log(cmd);
    exec(cmd);
}

function activityDocument(cb) {
    compile("ActivityDocument");
    cb();
}
function administrationPcru(cb) {
    compile("AdministrationPcru");
    cb();
}

exports.activityDocument = activityDocument;
exports.activityDocumentWatch = function(cb){
    watch(pathModuleSrc + "ActivityDocument.vue", activityDocument);
}

exports.administrationPcru = administrationPcru;
exports.administrationPcruWatch = function(cb){
    watch(pathModuleSrc + "AdministrationPcru.vue", administrationPcru);
}

exports.default = defaultTask;