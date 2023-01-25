/**
 * Configuration des dépendances Javascript dans le projet Oscar©.
 *
 * Created by jacksay on 17/09/15.
 */

let devext = window.MODE_DEV ? '' : '.min';

requirejs.config({
    baseUrl: "/js/",
    paths: {
        "bootbox": "vendor/bootbox/bootbox",
        "vis": "vendor/vis/dist/vis.min",
        "modalform": "oscar-modal-form",
        "backbone": "vendor/backbone/backbone-min",
        "Backbone": "vendor/backbone/backbone-min",
        "text": "vendor/requirejs-text/text",
        "domReady": "vendor/requirejs-domready/domReady",
        "handlebars": "vendor/handlebars/handlebars.min",
        "jquery": "vendor/jquery/dist/jquery.min",
        "jqueryui": "vendor/jqueryui/jquery-ui.min",
        "jqueryui-core": "vendor/jqueryui/ui/core",
        "jqueryui-widget": "vendor/jqueryui/ui/widget",
        "jquery-serialize": "vendor/jquery-serialize-object/dist/jquery.serialize-object.min",

        /** OLD **
        "moment": "vendor/momentjs/min/moment-with-locales.min",
        /*********/
        "moment": "vendor/momentjs/min/moment-with-locales.min",
        "moment-tz": "vendor/moment-timezone/builds/moment-timezone-with-data.min",

        "moment-timezone": "vendor/moment-timezone/builds/moment-timezone-with-data",
        "underscore": "vendor/underscore/underscore-min",
        "bt-datepicker": "vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min",
        "timepicker": "vendor/bootstrap-timepicker/js/bootstrap-timepicker",
        "bootstrap": "vendor/bootstrap/dist/js/bootstrap.min",
        "bootstrapLnf": "vendor/bootstrap-lnf/dist/js/bootstrap-lnf",
        "select2": "vendor/select2/dist/js/select2.min",
        "timewalker": "vendor/timewalker/src/js/TimeViewer",
        "oscar-auto": "oscar-auto",
        "OscarUI": "oscar-ui",
        "vue": "vendor/vue",
        "vue-resource": "vendor/vue-resource/dist/vue-resource",
        "vue-router": "vendor/vue-router/dist/vue-router",
        "vue-composition-api": "vendor/composition-api",
        "LocalDB": "LocalDB/LocalDB",
        "OscarDepenses": "OscarDepenses",
        "privileges": "components/build/privileges",
        "organizationrole": "oscar/dist/OrganizationRole",
        "ical": "vendor/ical.js/build/ical.min",
        "timesheet": "components/build/timesheet",
        "in-the-box" : "vendor/in-the-box/build/InTheBox",
        "papa-parse" : "vendor/papa-parse/papaparse.min",
        "colorpicker" : "vendor/bootstrapcolorpicker/dist/js/bootstrap-colorpicker.min",
        "calendar" : "modules/unicaen/dist/js/calendar",
        "calendar2" : "oscar/dist/Calendar",
        "CalendarModel" : "oscar/dist/CalendarModel",
        "EventDT" : "modules/unicaen/dist/js/EventDT",
        "ICalAnalyser" : "modules/unicaen/dist/js/ICalAnalyser",
        "Datepicker" : "modules/unicaen/dist/js/Datepicker",
        "KeySelectEditor" : "components/build/KeySelectEditor",
        "ActivitiesExport" : "oscar/dist/ActivitiesExport",
        "io" : "http://127.0.0.1:3000/socket.io/socket.io.js",
        "notifications" : "oscar/dist/Notification",
        "activity" : "components/build/activity",
        "TypeDocument" : "oscar/dist/TypeDocument",
        "milestones" : "oscar/dist/Milestones",
        "authentification" : "oscar/dist/Authentification",
        "payments" : "oscar/dist/Payments",
        "Datepicker2" : "oscar/dist/Datepicker",
        "activityclone" : "oscar/dist/Activityclone",
        "polyfill" : "oscar/dist/Polyfill",
        "ActivitySearchUi" : "oscar/dist/ActivitySearchUi.umd",
        "VueFilters" : "oscar/dist/VueFilters",
        "OrganizationType" : "oscar/dist/OrganizationType",
        "DisciplineUI" : "oscar/dist/DisciplineUI",
        "ValidationActivityVue" : "oscar/dist/ValidationActivityVue",
        "ValidationPeriod" : "oscar/dist/ValidationPeriod",
        "DeclarersList" : "oscar/dist/DeclarersList",
        "ImportIcalUI" : "oscar/dist/ImportIcalUI",
        "PeriodSelector" : "oscar/dist/PeriodSelector",
        "TimesheetDeclarationsList" : "oscar/dist/TimesheetDeclarationsList.umd",
        "PersonSchedule" : "oscar/dist/PersonSchedule",
        "TimesheetPersonResume" : "oscar/dist/TimesheetPersonResume",
        "ResumeActivity" : "oscar/dist/ResumeActivity",
        "PersonsList" : "oscar/dist/PersonsList",
        "Tva": "oscar/dist/Tva",
        "ActivityRequest": "oscar/dist/ActivityRequest",
        "ActivityRequestAdmin": "oscar/dist/ActivityRequestAdmin.umd",
        "ConfigStringList": "oscar/dist/ConfigStringList",
        "ActivityPersons": "oscar/dist/ActivityPersons",
        "ActivityGant": "oscar/dist/ActivityGant.umd",
        "Keyvalue": "oscar/dist/Keyvalue",
        "DocumentSectionAdmin": "oscar/dist/DocumentSectionAdmin",
        "SpentGroupAdmin": "oscar/dist/SpentGroupAdmin",
        "SpentGroupItem": "oscar/dist/SpentGroupItem",
        "EstimatedSpentActivity": "oscar/dist/EstimatedSpentActivity",
        "EstimatedSpentActivityItem": "oscar/dist/EstimatedSpentActivityItem",
        "Referent": "oscar/dist/Referent",
        "Declarers": "oscar/dist/Declarers",
        "ReferentUI": "oscar/dist/ReferentUI",
        "APIAccess": "oscar/dist/APIAccess",
        "EntityWithRole": "oscar/dist/EntityWithRole.umd.min",
        "SpentLinePFI": "oscar/dist/SpentLinePFI",
        "gestionprivileges": "oscar/dist/GestionPrivileges",
        "documentsactivity": "oscar/dist/DocumentsActivity",
        "activityspentsynthesis": "oscar/dist/ActivitySpentSynthesis",
        "rolesadminui": "oscar/dist/RolesAdminUI",
        "periodfieldsimple": "oscar/dist/PeriodFieldSimple",
        "activitydocument": "oscar/dist/ActivityDocument.umd.min",
        "administrationpcru": "oscar/dist/AdministrationPcru.umd",
        "administrationpcrupc": "oscar/dist/AdministrationPcruPoleCompetitivite.umd.min",
        "rnsrfield": "oscar/dist/RNSRField.umd.min",
        "contrattypepcru": "oscar/dist/ContratTypePCRU.umd.min",
        "createprocessuspcru": "oscar/dist/CreateProcessusPCRU.umd.min",
        "timesheetsynthesismenu": "oscar/dist/ActivityTimesheetSynthesisMenu.umd.min",
        "activityvalidator": "oscar/dist/ActivityValidator.umd.min",
        "workpackageui": "oscar/dist/WorkpackageUI.umd.min",
        "replacestrengthenperson": "oscar/dist/ReplaceStrengthenPerson.umd.min",
        "timesheetactivitysynthesis": "oscar/dist/TimesheetActivitySynthesis.umd"+devext,
        "timesheethighdelay": "oscar/dist/TimesheetHighDelay.umd"+devext,
        "activitytypeselector": "oscar/dist/ActivityTypeSelector.umd.min",
        "activitytypeitem": "oscar/dist/ActivityTypeItem.umd.min",
        "TimesheetMonth" : "oscar/dist/TimesheetMonth.umd.min",
        "ValidationUI": "oscar/dist/ValidationUI.umd.min",
        "AccountList": "oscar/dist/AccountList.umd.min",
        "NumberMigrate": "oscar/dist/NumberMigrate.umd.min"
    },
    shim: {
        "bootstrap": {
            deps: ["jquery"]
        },
        "oscar-auto": {
            deps: ["bootstrap"]
        },
        "CalendarModel": {
            exports: "CalendarModel"
        },
        "modalform": {
            deps: ["bootstrap"]
        },
        "backbone": {
            deps: ["underscore"],
            exports: "Backbone"
        },
        "jquery": {
            exports: "$"
        },
        "jquery-serialize": {
            deps: ["jquery"],
            exports: "$.fn.serialize"
        },
        "handlebars": {
            exports: "Handlebars"
        },
        "underscore": {
            exports: "_"
        },
        "moment": {
            exports: "moment"
        },
        "bt-datepicker": {
            deps: ["bootstrap"]
        },

        "datepicker": {
            deps: ["bootstrap"]
        },

        "timepicker": {
            deps: ["bootstrap"]
        },

        "colorpicker": {
            deps: ["bootstrap"]
        },
        "select2": {
            deps: ["jquery"]
        },
        "bootstrap": {
            deps: ["jquery"]
        },
        "bootstrapLnf": {
            deps: ["bootstrap"]
        },
        "ical": {
            export: "ICAL"
        },
        "moment-tz": {
            deps: ["moment"]
        },
        "calendar": {
            deps: ["EventDT", "vue", "ICalAnalyser", "Datepicker", "bootbox"]
        },
        "calendar2": {
            deps: ["moment", "EventDT", "vue", "ICalAnalyser", "Datepicker", "bootbox"]
        },
        "Datepicker": {
            deps: ["moment"]
        },
        "ICalAnalyser": {
            deps: ["ical", "moment-timezone"]
        },
        "moment-timezone": {
            exports: "tz",
            deps: ["moment"]
        },
        "EventDT": {
            deps: ["moment"],
            exports: "EventDT"
        }
    }
});

define("datepicker", ["jquery", "bootstrap", "bt-datepicker"], function ($) {
    $.fn.datepicker.dates["fr"] = {
        days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Diamanche"],
        daysShort: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"],
        daysMin: ["Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa", "Di"],
        months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Décembre"],
        monthsShort: ["Jan", "Fev", "Mar", "Avr", "Mai", "Jun", "Jul", "Aou", "Sep", "Oct", "Nov", "Dec"],
        today: "Aujourd'hui",
        clear: "Vider",
        weekStart: 1,
        calendarWeeks: true
    };
});

define("mm", ["moment"], function (moment) {
    console.log("Moment with timezone");
    moment.locale("fr");
    return moment;
});

function formatCurrency( amount ){
    var split, unit, fraction, i, j, formattedUnits, value,
        decimal = 2,
        decimalSeparator = ",",
        hundredSeparator = " ";

    // Format decimal
    value = amount.toFixed(decimal).toString();

    // split
    split = value.split(".");
    unit = split[0];
    fraction = split[1];
    formattedUnits = "";

    if( unit.length > 3 ){
        for( i=unit.length-1, j=0; i>=0; i--, j++ ){
            if( j%3 === 0 && i < unit.length-1 ){
                formattedUnits = hundredSeparator+ formattedUnits;
            }
            formattedUnits = unit[i]+formattedUnits;
        }
    } else {
        formattedUnits = unit;
    }
    return formattedUnits+decimalSeparator+fraction;
}

define("hbs", ["handlebars", "mm"], function (Handlebars, moment) {
    // Mise en forme des dates
    Handlebars.registerHelper("dateFormat", function (context, block) {
        if (moment) {
            var f = block.hash.format || "D MMMM YYYY";
            return moment(context).format(f);
        } else {
            return context;
        }
    });

    // Mise en forme des dates (Null)
    Handlebars.registerHelper("dateFormatNull", function (context, block) {
        if (moment) {
            var str;
            if( context && context.date ){
                var f = block.hash.format || "D MMMM YYYY";
                str = "<time datetime=\""+ context.date +"\">" + moment(context.date).format(f) + "</time>";
            } else {
                str = "<time class=\"date-missing\">Pas de date</time>";
            }
            return new Handlebars.SafeString(str);
        } else {
            return "Moment is missing";
        }
    });

    Handlebars.registerHelper("past", function(context, options){

        if(context.status == 1){
            if( !context.datePredicted )
                return "error";

            if( context.datePredicted.date < new Date().toISOString() )
                return "past";
        }
        return "";
    });

    Handlebars.registerHelper("currency", function (context, block) {
        return formatCurrency(context);
    });

    Handlebars.registerHelper("equal", function (v1, v2, options) {
        if (v1 === v2) {
            return options.fn(this);
        }
        return options.inverse(this);
    });

    // CSS en fonction des Rôles
    Handlebars.registerHelper("cssRole", function (context) {
        if (context.isLeader) {
            return "primary bordered";
        }
        else if (context.isMain) {
            return "secondary1";
        }
        else {
            return "default";
        }
    });
    return Handlebars;
});


(function () {
    require(["jquery", "bootstrap"], function () {
        require(["oscar-auto"], function () {
            Initer.isReady();
            return {};
        });
    });
})();
