<template>
  <div @mouseenter="handlerShow" @mouseleave="handlerHide">

    <div class="input-group">
      <input type="text" class="form-control" v-model="renderValue"/>
<!--      <span class="input-group-btn">-->
<!--        <button class="btn btn-default" type="button">Go!</button>-->
<!--      </span>-->
      <div class="input-group-addon">
        <i class="icon-calendar"></i>
      </div>
    </div>

    <div v-show="picker" class="datepicker-selector" @mouseleave="handlerHide">
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

export default {
  // Configuration
  model: {
    prop: 'modelValue',
    event: 'update:modelValue'
  },
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
      default: () => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
    },

    // Liste des mois utilisés dans l'UI
    months: {
      default: () => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
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
      realValue: this.value,
      manualChange: ""
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
      console.log('renderDate');
      if (this.realValue == null) {
        return "";
      } else {
        moment.locale('fr');
        return moment(this.realValue).format(this.format);
      }
    },

    /**
     * Rendu de la valeur courante en utilisant le format.
     *
     * @returns {string}
     */
    renderValue: {
      get() {
        return !this.realValue ? '' : this.mmValue.format(this.displayFormat);
      },
      set(text) {
        console.log('convert ', text);
        try {
          let v = moment(text, this.displayFormat);
          if (v.isValid())
            this.changeDate(v.format(this.valueFormat));
        } catch (e) {
          return;
        }
      }
    }
  },

  ////////////////////////////////////////////////////////////////////: METHODES
  methods: {
    /////////////////////////////////////////////////////////////////// HANDLERS
    handlerClear() {
      console.log(this.realValue);
      this.changeDate(null);
    },

    handlerInputChange(e) {

      try {
        var v = moment(e.target.value);
        this.changeDate(v.format(this.valueFormat));
      } catch (e) {
        console.error("WTF DATE", e);
      }

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
      console.log("Modification de la date", date);
      this.picker = false;
      this.realValue = date ? moment(date).format(this.valueFormat) : '';
      console.log("Update", this.realValue);
      this.$emit('update:modelValue', this.realValue);
      this.$emit('input', this.realValue);
      this.$emit('change', this.realValue);
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
      this.watchOut();
    },

    watchOut() {
      // console.log(document.querySelector('body'));
      //window.addEventListener('mouseup', this.handlerHide);
    },

    handlerHide(event) {
      //window.removeEventListener('mouseup', this.handlerHide);
      this.picker = false;
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
    this.pickerDayRef = this.value ? this.value : moment().format();
    this.initPickerVar();
  }
}
</script>