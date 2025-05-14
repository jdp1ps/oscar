<template>
  <div @mouseenter="handlerShow" @mouseleave="handlerHide" class="datepicker-component" :class="{'error' : hasError}">
    <div class="input-group">
      <input type="text" class="datepicker-input form-control" v-model="realInput"
             @focus="handlerFocus"
             @blur="handlerBlur"
             @keydown.enter="handlerKeyDownEnter"
             @keyup="handlerInputChange"
      />
      <div class="input-group-addon">
        <i class="icon-calendar"></i>
      </div>
    </div>

    <div v-show="picker && modeInput == 'picker'" class="datepicker-selector" @mouseleave="handlerHide" @mouseenter="handlerEnterPicker">
      <div class="datepicker-wrapper">
        <header>
          <nav>
                        <span href="#" @click.stop.prevent="pickerPrevMonth">
                            <i class="icon-angle-left"></i></span>
            <strong class="heading">
              <span class="currentMonth" @click.stop="handlerPickerMonth">{{ currentMonth }}</span>
              <span class="currentYear" @click.stop="handlerPickerYear">{{ currentYear }}</span>
            </strong>
            <span href="#" @click.stop="pickerNextMonth">
                            <i class="icon-angle-right"></i></span>
          </nav>
          <div class="day-labels week" v-if="pickerMode == 'day'">
            <span class="week-label">&nbsp;</span>
            <span class="day-label" v-for="d in pickerData.dayslabels">{{ d }}</span>
          </div>
        </header>

        <section v-if="pickerMode == 'day'">
          <div class="weeks" v-for="week in pickerData.weeks">
                            <span class="week">
                                <span class="week-label">{{ week.num }}</span>
                                <span class="week-day" v-for="d in week.days"
                                      :class="{ active: d.active, disabled: !d.enabled }"
                                      @click.prevent.stop="changeDate(d.date)">
                                  {{ d.day }}
                                </span>
                            </span>
          </div>
        </section>

        <section v-if="pickerMode == 'month'" class="months">
                        <span class="month" @click.prevent.stop="handlerSelectMonth(month)"
                              v-for="month in months"
                              :class="{ active: pickerMonthRef == month }">
                          {{ month }}
                        </span>
        </section>

        <section v-if="pickerMode == 'year'" class="years">
                        <span class="year"
                              @click.prevent.stop="pickerYearRef -= 22">&lt;&lt;</span>
          <span class="year"
                @click.prevent.stop="handlerSelectYear(year)"
                v-for="year in years"
                :class="{ active: pickerYearRef == year }">
                          {{ year }}
                        </span>
          <span class="year" @click.prevent.stop="pickerYearRef += 22">&gt;&gt;</span>
        </section>
      </div>
      <div style="text-align: center; cursor: pointer" @click="handlerClear">
        <i class="icon-cancel-alt"></i>
        Supprimer la date
      </div>
    </div>
  </div>

</template>

<script>

import moment from 'moment';
import 'moment/locale/fr';

moment.locale('fr');

const MODE_PICKER = 'picker';
const MODE_INPUT = 'input';

export default {
  // Configuration
  model: {
    prop: 'modelValue',
    event: 'update:modelValue'
  },
  props: {
    // Valeur par défaut
    modelValue: {
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
      default: () => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
    },

    // Liste des mois utilisés dans l'UI
    months: {
      default: () => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
    },

    // Format utilisé pour la valeur
    valueFormat: {
      default: 'YYYY-MM-DD'
    },

    // Format d'affichage
    displayFormat: {
      default: 'D MMMM YYYY'
    },

    // Format utilisé pour l'affichage
    format: {
      default: 'dddd D MMMM YYYY'
    }
  },

  data() {
    return {
      picker: false,
      pickerMode: 'day',
      pickerDayRef: moment().format(),
      pickerYearRef: moment().format('YYYY'),
      pickerMonthRef: moment().month(),
      realValue: this.modelValue,
      realInput: "",
      modeInput: MODE_PICKER,
      inputManual: false,
      hasError: false,
      tempo: null
    }
  },

  computed: {

    // Liste des années affichées dans le datepicker
    years() {
      let from = this.pickerYearRef - 11;
      let to = this.pickerYearRef + 11;
      let years = [];
      for (var i = from; i < to; i++) {
        years.push(i);
      }
      return years;
    },

    /**
     * Retourne la valeur active sous la forme d'un objet Moment.
     */
    mmValue() {
      if (this.realValue) {
        moment.locale('fr');
        return moment(this.realValue);
      } else {
        return moment();
      }
    },

    /**
     * Retourne les données utilisées pour afficher le selecteur de date en mode JOUR du MOIS.
     */
    pickerData() {
      // Make list of days
      var days = this.daysShort;

      var realValueFormatted = moment(this.realValue).format(this.valueFormat);
      // Début du mois
      let weekStart = moment(this.pickerDayRef).startOf('month').startOf('isoWeek');
      let weekEnd = moment(this.pickerDayRef).endOf('month').startOf('isoWeek');

      let datas = []
      for (; weekStart.unix() <= weekEnd.unix();) {
        let week = {
          num: weekStart.week(),
          days: []
        }
        for (let d = 1; d <= 7; d++) {
          let enabled = !this.limitFrom || (this.limitFrom && this.limitFrom < weekStart.format());
          week.days.push({
            enabled,
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
      }
    },

    currentMonth() {
      return moment(this.pickerDayRef).format('MMMM');
    },

    /**
     * Retourne l'année courante.
     *
     * @returns {string}
     */
    currentYear() {
      return moment(this.pickerDayRef).format('YYYY');
    },

    /**
     * Rendu de la date en utilisant de format 'humain'
     * @returns {*}
     */
    renderDate() {
      if (this.realValue == null) {
        return "";
      } else {
        moment.locale('fr');
        return moment(this.realValue).format(this.format);
      }
    }
  },

  ////////////////////////////////////////////////////////////////////: METHODES
  methods: {
    /////////////////////////////////////////////////////////////////// HANDLERS
    handlerClear() {
      this.changeDate("");
    },

    handlerFocus() {
      //this.modeInput = MODE_INPUT;
    },

    handlerBlur() {
      this.modeInput = MODE_PICKER;
    },

    handlerKeyDownEnter(e) {
      this.convertInput(e.target.value);
      this.modeInput = MODE_PICKER;
      e.target.blur();
    },

    /**
     * Conversion de la saisie en date
     * @param input
     */
    convertInput(input) {
      input = input.toLowerCase();
      input = input
          .replace('aout', 'Août')
          .replace('fevrier', 'Février')
          .replace('decembre', 'Décembre');

      let date = moment(input, ['D/M/YYYY','YYYY-M-D', 'YYYY-MM-DD', 'D MMMM YYYY'], true);
      if (date.isValid()) {
        let dateISO = date.format('YYYY-MM-DD');
        this.hasError = false;
        if (dateISO !== this.modelValue) {
          this.changeDate(dateISO);
        }
      } else {
        this.hasError = true;
      }
    },

    handlerInputChange(e) {
      this.modeInput = MODE_INPUT;
      if (this.tempo !== null) {
        clearTimeout(this.tempo);
      }
      this.tempo = setTimeout(() => {
        this.convertInput(e.target.value);
        clearTimeout(this.tempo);
      }, 1000);
    },

    /**
     * Déclenché quand un mois un selectionné.
     *
     * @param month
     */
    handlerSelectMonth(month) {
      let monthIndex = this.months.indexOf(month);
      this.pickerDayRef = moment(this.pickerDayRef).month(monthIndex).format();
      this.pickerMode = 'day';
    },

    /**
     * Déclanché quand une année est selectionnée.
     *
     * @param year
     */
    handlerSelectYear(year) {
      this.pickerDayRef = moment(this.pickerDayRef).year(year).format();
      this.pickerMode = 'day';
    },

    /**
     * Méthode à utiliser pour modifier la date saisie.
     */
    changeDate(date) {
      this.picker = false;

      let m = moment(date);

      if( date === "" ){
        this.hasError = false;
        this.realValue = "";
        this.realInput = "";
      } else {
        if (m.isValid()) {
          this.hasError = false;
          this.realValue = m.format(this.valueFormat);
          this.realInput = m.format(this.displayFormat);
        } else {
          this.realValue = '';
        }
      }

      this.$emit('update:modelValue', this.realValue);
      this.$emit('input', this.realValue);
      this.$emit('change', this.realValue);
      if (this.realValue) {
        this.pickerDayRef = this.realValue;
      }
      else {
        this.pickerDayRef = moment().format(this.valueFormat);
      }
      this.handlerHide();
    },

    /**
     * Déclenché lors d'un défilement vers le mois suivant
     */
    pickerNextMonth() {
      this.pickerDayRef = moment(this.pickerDayRef).add(1, 'month').format();
    },

    /**
     * Déclenché lors d'un défilement vers le mois précédent
     */
    pickerPrevMonth() {
      this.pickerDayRef = moment(this.pickerDayRef).subtract(1, 'month').format();
    },

    /**
     * Affichage du selecteur de mois.
     */
    handlerPickerMonth(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      e.stopPropagation();
      this.pickerMode = 'month';
    },

    /**
     * Affichage du selecteur d'année.
     */
    handlerPickerYear() {
      this.pickerMode = 'year';
    },

    handlerShow() {
      this.initPickerVar();
      this.picker = true;
    },

    handlerHide(event) {
      //window.removeEventListener('mouseup', this.handlerHide);
      this.picker = false;
    },

    handlerLeavePicker(event) {
      this.modeInput = MODE_INPUT;
      this.picker = false;
    },

    handlerEnterPicker(event) {
      this.modeInput = MODE_PICKER;
    },

    /**
     * Initialisation des données pour l'affichage du picker.
     */
    initPickerVar() {
      var ref = moment(this.pickerDayRef ? this.pickerDayRef : moment());
      this.pickerYearRef = ref.year();
      this.pickerMonthRef = ref.format('MMMM');
    }
  },

  created() {
    moment.locale(this.i18n);
    this.pickerDayRef = this.modelValue ? this.modelValue : moment().format();
    this.realInput = this.modelValue ? moment(this.modelValue).format(this.displayFormat) : "";
    this.initPickerVar();
  }
}
</script>

<style lang="scss">


////////////////////////////////// DATE PICKER
.datepicker-component {
  &.error {
    .datepicker-input {
      border-color: darkred !important;
    }

    .input-group-addon {
      color: darkred;
    }
  }
}

.datepicker-selector {
  position: absolute;
  width: 20em;
  z-index: 1000;
  background: white;
  font-size: .9em;
  box-shadow: 0 0 1em rgba(0, 0, 0, 0.25);
  border: thin solid rgba(0, 0, 0, 0.5);

  .week-label, .week-day, .day-label, .week-label {
    flex: 0 0 50px;
  }

  .currentMonth, .currentYear {
    padding: .3em;
  }

  .datepicker-wrapper {
    position: relative;
    z-index: 900;
    background: white;
    padding: 4px;
  }

  &:before {
    content: '';
    position: absolute;
    background: white;
    border: thin solid rgba(0, 0, 0, 0.5);
    width: 16px;
    left: 16px;
    height: 16px;
    transform: rotate(45deg);
    top: -8px;
    z-index: 800;
  }

  header nav {
    display: flex;

    .heading {
      white-space: nowrap;
      text-align: center;
    }

    .currentMonth, .currentYear {
      cursor: pointer;

      &:hover {
        background: rgba(255, 0, 0, 0.5);
      }
    }

    > * {
      flex: 1;
    }

    > span {
      flex: 0;
      cursor: pointer;

      &:hover {
        background: rgba(255, 0, 0, 0.5);
      }
    }
  }

  .day-labels {
    display: flex;
    align-items: stretch;

    .day-label {
      color: rgba(0, 0, 0, 0.5);
      flex: 1;
    }
  }

  .months, .years, .week {
    display: flex;
    flex-wrap: wrap;
    cursor: pointer;
  }

  .months .month {
    flex: 1;
    width: 33.3333333333333333%;
    padding: .3em 0;
  }
}

.datepicker-selector .months .month, .datepicker-selector .months .year, .datepicker-selector .months .week-day, .datepicker-selector .years .month, .datepicker-selector .years .year, .datepicker-selector .years .week-day, .datepicker-selector .week .month, .datepicker-selector .week .year, .datepicker-selector .week .week-day {
  padding: .3em;
  flex: 1;
  text-align: center;
  border-radius: 4px;
}

.datepicker-selector .months .month:hover, .datepicker-selector .months .year:hover, .datepicker-selector .months .week-day:hover, .datepicker-selector .years .month:hover, .datepicker-selector .years .year:hover, .datepicker-selector .years .week-day:hover, .datepicker-selector .week .month:hover, .datepicker-selector .week .year:hover, .datepicker-selector .week .week-day:hover {
  background: rgba(255, 0, 0, 0.9);
  color: white;
}

.datepicker-selector .months .month.active, .datepicker-selector .months .year.active, .datepicker-selector .months .week-day.active, .datepicker-selector .years .month.active, .datepicker-selector .years .year.active, .datepicker-selector .years .week-day.active, .datepicker-selector .week .month.active, .datepicker-selector .week .year.active, .datepicker-selector .week .week-day.active {
  background: rgba(255, 0, 0, 0.4) !important;
  text-shadow: -1px 1px 0 rgba(0, 0, 0, 0.3);
  color: white;
}

.datepicker-selector .months .month.disabled, .datepicker-selector .months .year.disabled, .datepicker-selector .months .week-day.disabled, .datepicker-selector .years .month.disabled, .datepicker-selector .years .year.disabled, .datepicker-selector .years .week-day.disabled, .datepicker-selector .week .month.disabled, .datepicker-selector .week .year.disabled, .datepicker-selector .week .week-day.disabled {
  background: white !important;
  color: rgba(0, 0, 0, 0.2);
  pointer-events: none;
}

.datepicker-selector .week {
  display: flex;
  align-items: stretch;
  line-height: 1em;
}

.datepicker-selector .week .week-label {
  font-size: .8em;
  flex: 1;
  color: rgba(0, 0, 0, 0.5);
}

.datepicker-selector .week .week-day {
  text-align: center;
  flex: 1;
  padding: 4px;
  cursor: pointer;
}

.datepicker-selector .week .week-day:hover {
  background-color: rgba(255, 0, 0, 0.75) !important;
  color: white;
}

.datepicker-selector .week .week-day:nth-child(odd) {
  background: rgba(0, 0, 0, 0.05);
}

.list-selector {
  display: block;

  div {
    display: block;

    article {
      cursor: pointer;
      display: block;
      align-items: center;
      justify-items: center;

      &:nth-child(even) {
        background: rgba(#0a3783, .05);
      }

      &:hover {
        background: rgba(#9CEFE8, .8);
      }

      strong {
        flex: 5;
      }

      small {
        margin-left: auto;
        flex: 0;
      }
    }
  }
}
</style>