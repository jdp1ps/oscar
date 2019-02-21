<template>
    <div @mouseenter="handlerShow" @mouseleave="handlerHide">

        <div class="input-group">
            <input type="text" class="form-control" v-model="renderValue"/>
            <div class="input-group-addon">
                <i class="icon-calendar"></i>
            </div>
        </div>

        <div class="datepicker-selector" @mouseleave="handlerHide" v-show="picker">
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
                                <span class="week-day" v-for="d in week.days" :class="{ active: d.active, disabled: !d.enabled }"
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
    export default {
        // Configuration
        model: {
            prop: 'value',
            event: 'input'
        },
        props: {
            moment: {
                required: true
            },

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
                pickerDayRef: this.moment().format(),
                pickerYearRef: this.moment().format('YYYY'),
                pickerMonthRef: this.moment().month(),
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
                if (this.realValue)
                    return this.moment(this.realValue);
                else
                    return this.moment();
            },

            /**
             * Retourne les données utilisées pour afficher le selecteur de date en mode JOUR du MOIS.
             */
            pickerData() {
                this.moment.locale(this.i18n);

                // Make list of days
                var days = this.daysShort;

                var realValueFormatted = this.moment(this.realValue).format(this.valueFormat);
                // Début du mois
                let weekStart = this.moment(this.pickerDayRef).startOf('month').startOf('isoWeek');
                let weekEnd = this.moment(this.pickerDayRef).endOf('month').startOf('isoWeek');

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
                return this.moment(this.pickerDayRef).format('MMMM');
            },

            /**
             * Retourne l'année courante.
             *
             * @returns {string}
             */
            currentYear() {
                return this.moment(this.pickerDayRef).format('YYYY');
            },

            /**
             * Rendu de la date en utilisant de format 'humain'
             * @returns {*}
             */
            renderDate() {
                if (this.realValue == null) {
                    return ""
                }
                else {
                    return this.moment(this.realValue).format(this.format)
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
                set( text ){
                    console.log('convert ', text);
                    try {
                        let v = this.moment(text, this.displayFormat);
                        if( v.isValid() )
                            this.changeDate(v.format(this.valueFormat));
                    }catch (e) {
                        return;
                    }
                }
            }
        },

        ////////////////////////////////////////////////////////////////////: METHODES
        methods: {
            /////////////////////////////////////////////////////////////////// HANDLERS
            handlerClear(){
                console.log(this.realValue);
                this.changeDate(null);
            },

            handlerInputChange(e){

                try {
                    var v = this.moment(e.target.value);
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
                this.pickerDayRef = this.moment(this.pickerDayRef).month(monthIndex).format();
                this.pickerMode = 'day';
            },

            /**
             * Déclanché quand une année est selectionnée.
             *
             * @param year
             */
            handlerSelectYear(year) {
                this.pickerDayRef = this.moment(this.pickerDayRef).year(year).format();
                this.pickerMode = 'day';
            },

            /**
             * Méthode à utiliser pour modifier la date saisie.
             */
            changeDate(date) {
                console.log("Modification de la date", date);
                this.picker = false;
                this.realValue = date;
                this.$emit('input', this.realValue);
                this.$emit('change', this.realValue);
                this.handlerHide();
            },

            /**
             * Déclenché lors d'un défilement vers le mois suivant
             */
            pickerNextMonth() {
                this.pickerDayRef = this.moment(this.pickerDayRef).add(1, 'month').format();
            },

            /**
             * Déclenché lors d'un défilement vers le mois précédent
             */
            pickerPrevMonth() {
                this.pickerDayRef = this.moment(this.pickerDayRef).subtract(1, 'month').format();
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
                var ref = this.moment(this.pickerDayRef ? this.pickerDayRef : moment());
                this.pickerYearRef = ref.year();
                this.pickerMonthRef = ref.format('MMMM');
            }
        },

        created() {
            this.moment.locale(this.i18n);
            this.pickerDayRef = this.value ? this.value : this.moment().format();
            this.initPickerVar();
        }
    }
</script>

<style lang="scss" scoped>
    .datepicker-selector {
        position: absolute;
        width: 16em;
        z-index: 1000;
        background: white;
        font-size: .9em;
        box-shadow: 0 0 1em rgba(0, 0, 0, 0.25);
        border: thin solid rgba(0, 0, 0, 0.5);
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

</style>