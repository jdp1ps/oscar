/**
 * Configuration des dépendances Javascript dans le projet Oscar©.
 *
 * Created by jacksay on 17/09/15.
 */

requirejs.config({
    baseUrl: '/js/',
    paths: {
        'bootbox': 'vendor/bootbox/bootbox',
        'vis': 'vendor/vis/dist/vis.min',
        'modalform': 'oscar-modal-form',
        'backbone': 'vendor/backbone/backbone-min',
        'Backbone': 'vendor/backbone/backbone-min',
        'text': 'vendor/requirejs-text/text',
        'domReady': 'vendor/requirejs-domready/domReady',
        'handlebars': 'vendor/handlebars/handlebars.min',
        'jquery': 'vendor/jquery/dist/jquery.min',
        'jqueryui': 'vendor/jqueryui/jquery-ui.min',
        'jqueryui-core': 'vendor/jqueryui/ui/core',
        'jqueryui-widget': 'vendor/jqueryui/ui/widget',
        'jquery-serialize': 'vendor/jquery-serialize-object/dist/jquery.serialize-object.min',
        'moment': 'vendor/momentjs/min/moment-with-locales.min',
        'moment-tz': 'vendor/moment-timezone/builds/moment-timezone-with-data.min',
        'moment-timezone': 'vendor/moment-timezone/builds/moment-timezone-with-data',
        'underscore': 'vendor/underscore/underscore-min',
        'bt-datepicker': 'vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min',
        'timepicker': 'vendor/bootstrap-timepicker/js/bootstrap-timepicker',
        'bootstrap': 'vendor/bootstrap/dist/js/bootstrap.min',
        'bootstrapLnf': 'vendor/bootstrap-lnf/dist/js/bootstrap-lnf',
        'select2': 'vendor/select2/dist/js/select2.min',
        'timewalker': 'vendor/timewalker/src/js/TimeViewer',
        'oscar-auto': 'oscar-auto',
        'OscarUI': 'oscar-ui',
        'vue': 'vendor/vue/dist/vue',
        'vue-resource': 'vendor/vue-resource/dist/vue-resource',
        'vue-router': 'vendor/vue-router/dist/vue-router',
        'LocalDB': 'LocalDB/LocalDB',
        'OscarDepenses': 'OscarDepenses',
        'privileges': 'components/build/privileges',
        'organizationrole': 'components/build/organizationrole',
        'roles': 'components/build/roles',
        'workpackageperson': 'components/build/workpackageperson',
        'ical': 'vendor/ical.js/build/ical.min',
        'timesheet': 'components/build/timesheet',
        'in-the-box' : 'vendor/in-the-box/build/InTheBox',
        'papa-parse' : 'vendor/papa-parse/papaparse.min',
        'colorpicker' : 'vendor/bootstrapcolorpicker/dist/js/bootstrap-colorpicker.min',
        'calendar' : 'vuejs-calendar/build/js/calendar',
        'EventDT' : 'vuejs-calendar/build/js/EventDT',
        'ICalAnalyser' : 'vuejs-calendar/build/js/ICalAnalyser',
        'Datepicker' : 'vuesjs-components/dist/js/Datepicker'
    },
    shim: {
        "bootstrap": {
            deps: ['jquery']
        },
        "oscar-auto": {
            deps: ['bootstrap']
        },
        "modalform": {
            deps: ['bootstrap']
        },
        backbone: {
            deps: ['underscore'],
            exports: 'Backbone'
        },
        jquery: {
            exports: '$'
        },
        'jquery-serialize': {
            deps: ['jquery'],
            exports: '$.fn.serialize'
        },
        handlebars: {
            exports: 'Handlebars'
        },
        underscore: {
            exports: '_'
        },
        moment: {
            exports: 'moment'
        },
        'bt-datepicker': {
            deps: ['bootstrap']
        },

        datepicker: {
            deps: ['bootstrap']
        },

        timepicker: {
            deps: ['bootstrap']
        },

        colorpicker: {
            deps: ['bootstrap']
        },
        select2: {
            deps: ['jquery']
        },
        bootstrap: {
            deps: ['jquery']
        },
        bootstrapLnf: {
            deps: ['bootstrap']
        },
        ical: {
            export: 'ICAL'
        },
        'moment-tz': {
            deps: ['moment']
        },
        'calendar': {
            deps: ['EventDT', 'vue', 'ICalAnalyser', 'Datepicker']
        },
        'Datepicker': {
            deps: ['moment-tz']
        },
        'ICalAnalyser': {
            deps: ['ical', 'moment-timezone']
        },
        'moment-timezone': {
//            exports: 'tz',
            deps: ['moment']
        },
        'EventDT': {
            deps: ['moment'],
            exports: 'EventDT'
        }
    }
});

define('datepicker', ['jquery', 'bootstrap', 'bt-datepicker'], function ($) {
    $.fn.datepicker.dates['fr'] = {
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

define('mm', ['moment-tz'], function (moment) {
    moment.locale('fr');
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
    split = value.split('.');
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

define('hbs', ['handlebars', 'mm'], function (Handlebars, moment) {
    // Mise en forme des dates
    Handlebars.registerHelper('dateFormat', function (context, block) {
        if (moment) {
            var f = block.hash.format || 'D MMMM YYYY';
            return moment(context).format(f);
        } else {
            return context;
        }
    });

    // Mise en forme des dates (Null)
    Handlebars.registerHelper('dateFormatNull', function (context, block) {
        if (moment) {
            var str;
            if( context && context.date ){
                var f = block.hash.format || 'D MMMM YYYY';
                str = '<time datetime="'+ context.date +'">' + moment(context.date).format(f) + '</time>';
            } else {
                str = '<time class="date-missing">Pas de date</time>';
            }
            return new Handlebars.SafeString(str);
        } else {
            return 'Moment is missing';
        }
    });

    Handlebars.registerHelper('past', function(context, options){
        if(context.status == 1 && context.datePredicted.date < new Date().toISOString()){
            return "past";
        }
        return "";
    });

    Handlebars.registerHelper('currency', function (context, block) {
        return formatCurrency(context);
    });

    Handlebars.registerHelper('equal', function (v1, v2, options) {
        if (v1 === v2) {
            return options.fn(this);
        }
        return options.inverse(this);
    });

    // CSS en fonction des Rôles
    Handlebars.registerHelper('cssRole', function (context) {
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
    require(['jquery', 'bootstrap'], function () {
        require(['oscar-auto'], function () {
            Initer.isReady();
            return {};
        });
    });
})();
