const { watch, src, dest } = require('gulp');
const exec = require('child_process').exec;

const pathModuleSrc = './src/';
const pathModuleDest = './../public/js/oscar/dist/'

function compile(module){
    let moduleSrc = pathModuleSrc + module + ".vue";
    let moduleDest = pathModuleDest;
    console.log("Compile ", module, " from ", moduleSrc, " to ", moduleDest);
    let cmd = "node node_modules/.bin/vue-cli-service build --name "+module+" --dest " + moduleDest + " --no-clean --formats umd,umd-min --target lib " + moduleSrc;
    console.log(cmd);
    exec(cmd);
}

function compileComponent(componentBaseName){
    let moduleSrc = './src/components/' + componentBaseName + ".vue";
    let moduleDest = pathModuleDest;
    console.log("Compile ", componentBaseName, " from ", moduleSrc, " to ", moduleDest);
    let cmd = "node node_modules/.bin/vue-cli-service build --name "+componentBaseName+" --dest " + moduleDest + " --no-clean --formats umd,umd-min --target lib " + moduleSrc;
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


let commands = ['activityDocument', 'administrationPcru'];


exports.activityDocument = activityDocument;
exports.activityDocumentWatch = function(cb){
    watch(pathModuleSrc + "ActivityDocument.vue", activityDocument);
}

exports.administrationPcru = administrationPcru;
exports.administrationPcruWatch = function(cb){
    watch(pathModuleSrc + "AdministrationPcru.vue", administrationPcru);
}

///////////////////////// COMPOSANTS COMPILES
function componentPassword(cb) {
    compileComponent('PasswordField');
    cb();
}
exports.componentPassword = componentPassword;
exports.componentPasswordWatch = function(cb){ watch('./src/components/PasswordField.vue', componentPassword); }

function componentTextarea(cb) {
    compileComponent('TextareaField');
    cb();
}
exports.componentTextarea = componentTextarea;
exports.componentTextareaWatch = function(cb){ watch('./src/components/TextareaField.vue', componentTextarea); }

function defaultTask(cb) {
    console.log("Usage : ");
    for( let i=0; i<commands.length; i++ ){
        console.log(commands[i], " : ");
        console.log(" - compile > node_modules/.bin/gulp " + commands[i]);
        console.log(" - watch   > node_modules/.bin/gulp " + commands[i] + 'Watch');
    }
    cb();
}

exports.default = defaultTask;