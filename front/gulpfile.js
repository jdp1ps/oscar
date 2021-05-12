const { watch, src, dest } = require('gulp');
const exec = require('child_process').exec;

const pathModuleSrc = './src/';
const pathModuleDest = './../public/js/oscar/dist/'


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions génériques

/**
 * Compilation d'un module
 * @param module
 */
function compile(module){
    let moduleSrc = pathModuleSrc + module + ".vue";
    let moduleDest = pathModuleDest;
    console.log("Compile ", module, " from ", moduleSrc, " to ", moduleDest);
    let cmd = "node node_modules/.bin/vue-cli-service build --name "+module+" --dest " + moduleDest + " --no-clean --formats umd,umd-min --target lib " + moduleSrc;
    console.log(cmd);
    exec(cmd);
}

/**
 * Compilation d'un composant
 * @param module
 */
function compileComponent(componentBaseName){
    let moduleSrc = './src/components/' + componentBaseName + ".vue";
    let moduleDest = pathModuleDest;
    console.log("Compile ", componentBaseName, " from ", moduleSrc, " to ", moduleDest);
    let cmd = "node node_modules/.bin/vue-cli-service build --name "+componentBaseName+" --dest " + moduleDest + " --no-clean --formats umd,umd-min --target lib " + moduleSrc;
    console.log(cmd);
    exec(cmd);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// COMMANDES GULP
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//// 2. MODULES
//
// 2.1 UI de configuration PCRU (FTP)
function administrationPcru(cb) {
    compile("AdministrationPcru");
    cb();
}
exports.administrationPcru = administrationPcru;
exports.administrationPcruWatch = function(cb){
    watch(pathModuleSrc + "AdministrationPcru.vue", administrationPcru);
}

//// 2.2 UI de configuration des pôles de compétitivité PCRU
function administrationPcruPC(cb) {
    compile("AdministrationPcruPoleCompetitivite");
    cb();
}
exports.administrationPCPcru = administrationPcruPC;
exports.administrationPCPcruWatch = function(cb){
    watch(pathModuleSrc + "AdministrationPcruPoleCompetitivite.vue", administrationPcruPC);
}

//// 2.3 Documents des activités
function activityDocument(cb) {
    compile("ActivityDocument");
    cb();
}
exports.activityDocument = activityDocument;
exports.activityDocumentWatch = function(cb){
    watch(pathModuleSrc + "ActivityDocument.vue", activityDocument);
}

//// 2.4 Correspondance Type de contrat PCRU / Type d'activité Oscar
function contratTypePCRU(cb) {
    compile("ContratTypePCRU");
    cb();
}
exports.contratTypePCRU = contratTypePCRU;
exports.contratTypePCRUWatch = function(cb){
    watch(pathModuleSrc + "ContratTypePCRU.vue", contratTypePCRU);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//// 3. COMPOSANTS
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

function componentRNSRField(cb) {
    compileComponent('RNSRField');
    cb();
}
exports.componentRNSRField = componentRNSRField;
exports.componentRNSRFieldWatch = function(cb){ watch('./src/components/RNSRField.vue', componentRNSRField); }

let commands = ['activityDocument', 'administrationPcru', 'administrationPcruPC', 'componentRNSRField'];

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