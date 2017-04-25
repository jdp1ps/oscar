;(function(root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['moment'], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory(require('moment'));
  } else {
    root.Datepicker = factory(root.moment);
  }
}(this, function(moment) {
'use strict';

var Datepicker = {
    template: '<div @mouseenter="handlerShow" @mouseleave="handlerHide">\n    <span>\n      Affichage de la date Human Friendly : {{ renderDate }}\n    </span>\n\n    <div class="input-group">\n      <input type="text" class="form-control" :value="renderValue"/>\n      <div class="input-group-addon">\n        <i class="glyphicon glyphicon-calendar"></i>\n      </div>\n    </div>\n\n    <div class="datepicker-selector" v-if="picker" @mouseleave="handlerHide">\n      <div class="datepicker-wrapper">\n      <header>\n        <nav>\n          <span href="#" @click.stop.prevent="pickerPrevMonth">\n            <i class="glyphicon glyphicon-chevron-left"></i>\n          </span>\n          <strong class="heading">\n              <span class="currentMonth"\n                @click.stop="handlerPickerMonth">\n                {{ currentMonth }}\n              </span>\n              <span class="currentYear"\n                @click.stop="handlerPickerYear">\n               {{ currentYear }}\n               </span>\n          </strong>\n          <span href="#"  @click.stop="pickerNextMonth">\n            <i class="glyphicon glyphicon-chevron-right"></i>\n          </span>\n        </nav>\n\n        <div class="day-labels week" v-if="pickerMode == \'day\'">\n          <span class="week-label">&nbsp;</span>\n          <span class="day-label" v-for="d in pickerData.dayslabels">\n            {{ d }}\n          </span>\n        </div>\n\n      </header>\n\n      <section v-if="pickerMode == \'day\'">\n        <div class="weeks" v-for="week in pickerData.weeks">\n          <span class="week">\n            <span class="week-label">{{ week.num }}</span>\n            <span class="week-day" v-for="d in week.days" :class="{ active: d.active, disabled: !d.enabled }"\n              @click.prevent.stop="changeDate(d.date)">\n              {{ d.day }}\n            </span>\n          </span>\n        </div>\n      </section>\n\n      <section v-if="pickerMode == \'month\'" class="months">\n        <span class="month" @click.prevent.stop="handlerSelectMonth(month)" v-for="month in months" :class="{ active: pickerMonthRef == month }">\n          {{ month }}\n        </span>\n      </section>\n\n      <section v-if="pickerMode == \'year\'" class="years">\n        <span class="year" @click.prevent.stop="pickerYearRef -= 22">&lt;&lt;</span>\n        <span class="year" @click.prevent.stop="handlerSelectYear(year)" v-for="year in years" :class="{ active: pickerYearRef == year }">\n          {{ year }}\n        </span>\n        <span class="year" @click.prevent.stop="pickerYearRef += 22">&gt;&gt;</span>\n      </section>\n\n      </div>\n    </div>\n    </div>\n  ',

    // Configuration
    props: {
        // Valeur par défaut
        value: {
            default: null
        },

        // Deprecated
        i18n: {
            default: "fr"
        },

        limitFrom: {
            default: null
        },

        // Liste des jours utilisés dans l'UI
        daysShort: {
            default: function _default() {
                return ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            }
        },

        // Liste des mois utilisés dans l'UI
        months: {
            default: function _default() {
                return ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            }
        },

        // Format utilisé pour la valeur
        valueFormat: {
            default: 'YYYY-MM-DD'
        },

        // Format utilisé pour l'affichage
        format: {
            default: 'dddd D MMMM YYYY'
        }
    },

    data: function data() {
        return {
            picker: false,
            pickerMode: 'day',
            pickerDayRef: moment().format(),
            pickerYearRef: moment().format('YYYY'),
            pickerMonthRef: moment().month(),
            realValue: this.value
        };
    },


    computed: {

        // Liste des années affichées dans le datepicker
        years: function years() {
            var from = this.pickerYearRef - 11;
            var to = this.pickerYearRef + 11;
            var years = [];
            for (var i = from; i < to; i++) {
                years.push(i);
            }
            return years;
        },


        /**
         * Retourne la valeur active sous la forme d'un objet Moment.
         */
        mmValue: function mmValue() {
            if (this.realValue) return moment(this.realValue);else return moment();
        },


        /**
         * Retourne les données utilisées pour afficher le selecteur de date en mode JOUR du MOIS.
         */
        pickerData: function pickerData() {
            moment.locale(this.i18n);

            // Make list of days
            var days = this.daysShort;

            var realValueFormatted = moment(this.realValue).format(this.valueFormat);
            // Début du mois
            var weekStart = moment(this.pickerDayRef).startOf('month').startOf('isoWeek');
            var weekEnd = moment(this.pickerDayRef).endOf('month').startOf('isoWeek');

            var datas = [];
            for (; weekStart.unix() <= weekEnd.unix();) {
                var week = {
                    num: weekStart.week(),
                    days: []
                };
                for (var d = 1; d <= 7; d++) {
                    var enabled = !this.limitFrom || this.limitFrom && this.limitFrom < weekStart.format();
                    week.days.push({
                        enabled: enabled,
                        date: weekStart.format(),
                        active: weekStart.format(this.valueFormat) == realValueFormatted,
                        day: weekStart.format('D')
                    });
                    weekStart.add(1, 'day');
                }
                datas.push(week);
            }
            return {
                dayslabels: days,
                weeks: datas
            };
        },
        currentMonth: function currentMonth() {
            return moment(this.pickerDayRef).format('MMMM');
        },


        /**
         * Retourne l'année courante.
         *
         * @returns {string}
         */
        currentYear: function currentYear() {
            return moment(this.pickerDayRef).format('YYYY');
        },


        /**
         * Rendu de la date en utilisant de format 'humain'
         * @returns {*}
         */
        renderDate: function renderDate() {
            if (this.realValue == null) {
                return "";
            } else {
                return moment(this.realValue).format(this.format);
            }
        },


        /**
         * Rendu de la valeur courante en utilisant le format.
         *
         * @returns {string}
         */
        renderValue: function renderValue() {
            return !this.realValue ? '' : this.mmValue.format(this.valueFormat);
        }
    },

    ////////////////////////////////////////////////////////////////////: METHODES
    methods: {
        /////////////////////////////////////////////////////////////////// HANDLERS

        /**
         * Déclenché quand un mois un selectionné.
         *
         * @param month
         */
        handlerSelectMonth: function handlerSelectMonth(month) {
            var monthIndex = this.months.indexOf(month);
            this.pickerDayRef = moment(this.pickerDayRef).month(monthIndex).format();
            this.pickerMode = 'day';
        },


        /**
         * Déclanché quand une année est selectionnée.
         *
         * @param year
         */
        handlerSelectYear: function handlerSelectYear(year) {
            this.pickerDayRef = moment(this.pickerDayRef).year(year).format();
            this.pickerMode = 'day';
        },


        /**
         * Méthode à utiliser pour modifier la date saisie.
         */
        changeDate: function changeDate(date) {
            this.picker = false;
            this.realValue = date;
            this.$emit('input', this.realValue);
            this.$emit('change', this.realValue);
            this.handlerHide();
        },


        /**
         * Déclenché lors d'un défilement vers le mois suivant
         */
        pickerNextMonth: function pickerNextMonth() {
            this.pickerDayRef = moment(this.pickerDayRef).add(1, 'month').format();
        },


        /**
         * Déclenché lors d'un défilement vers le mois précédent
         */
        pickerPrevMonth: function pickerPrevMonth() {
            this.pickerDayRef = moment(this.pickerDayRef).subtract(1, 'month').format();
        },


        /**
         * Affichage du selecteur de mois.
         */
        handlerPickerMonth: function handlerPickerMonth(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
            this.pickerMode = 'month';
        },


        /**
         * Affichage du selecteur d'année.
         */
        handlerPickerYear: function handlerPickerYear() {
            this.pickerMode = 'year';
        },
        handlerShow: function handlerShow() {

            this.initPickerVar();
            this.picker = true;
            this.watchOut();
        },
        watchOut: function watchOut() {
            // console.log(document.querySelector('body'));
            //window.addEventListener('mouseup', this.handlerHide);
        },
        handlerHide: function handlerHide(event) {
            //window.removeEventListener('mouseup', this.handlerHide);
            this.picker = false;
        },


        /**
         * Initialisation des données pour l'affichage du picker.
         */
        initPickerVar: function initPickerVar() {
            var ref = moment(this.pickerDayRef ? this.pickerDayRef : moment());
            this.pickerYearRef = ref.year();
            this.pickerMonthRef = ref.format('MMMM');
        }
    },

    created: function created() {
        console.log('TEST');
        moment.locale(this.i18n);
        this.pickerDayRef = this.value ? this.value : moment().format();
        this.initPickerVar();
    }
};
return Datepicker;
}));
