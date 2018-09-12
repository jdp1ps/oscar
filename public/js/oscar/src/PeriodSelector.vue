<template>
    <span class="period-selector" :class="{ 'open': showYear || showMonth }">
        <pre>pre: {{ period }}</pre>
        <span class="visualizer" @click.prevent.stop="handlerToggleSelector">
            <span class="month">
                {{ displayedMonth }}
            </span>
            <span class="year">
                {{ displayedYear }}
            </span>
            <i class="icon-angle-down"></i>
        </span>
        <span class="selector selector-year" v-if="showMonth">
            <span v-for="y in selectableYear" @click.prevent.stop="handlerSelectYear(y)" :class="{ 'selected': y == selectedYear }">
                {{ y }}
            </span>
        </span>
        <span class="selector selector-month" v-if="showYear">
            <span v-for="m,i in selectableMonths" @click.prevent.stop="handlerSelectMonth(i)" :class="{ 'selected': i == selectedMonth }">
                {{ m }}
            </span>
        </span>
    </span>
</template>

<style scoped>
    .period-selector {
        position: relative;
    }

    .visualizer {
        border: solid thin #8f97a0;
        border-radius: 4px;
        display: inline-flex;
        background: white;
        z-index: 11;
        position: relative;
    }

    .open .visualizer {
        border-radius: 4px 4px 0 0;
        border-bottom: none;
    }

    .visualizer:hover {
        background: #5c9ccc;
    }

    .visualizer span {
        display: inline-block;
        padding: 4px 8px;
    }

    .period-selector .visualizer { display: inline-flex; }

    .selector {
        z-index: 10;
        width: 30em;
        height: 6em;
        position: absolute;
        display: flex;
        flex-direction: row;
        flex-flow: wrap;
        background: white;
        box-shadow: 0 .5em .3em rgba(0,0,0,.3);
        border: solid thin #8f97a0;
        margin-top: -1px;
    }
    .selector span {
        flex: 0 0 25%;
        background: rgba(255,255,255,.5);
        padding: 4px;
        text-align: center;
    }
    .selector span:hover {
        background: #5c9ccc;
        cursor: pointer;
    }
    .selector span.selected {
        background: #5c9ccc;
        color: white;
        text-shadow: -1px 1px 1px rgba(255,255,255,.5);
    }
</style>

<script>
    // poi watch --format umd --moduleName  PeriodSelector --filename.css PeriodSelector.css --filename.js PeriodSelector.js --dist public/js/oscar/dist public/js/oscar/src/PeriodSelector.vue

    var regex = new RegExp(/([0-9]*)-([0-9]*)/);

    export default {

        model: {
            prop: 'period',
            event: 'change'
        },

        data(){
          return {
              selectedYear: null,
              selectedMonth: null,
              middleYearSelector: null,
              showYearSelector: false,
              showMonthSelector: false,
              showMonth: false,
              showYear: false
          }
        },

        props: {
            value: { default: ""},
            months: { default: function(){ return ["Janvier", 'Février', "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"] } },
            period: { default: null }
        },

        computed: {
            displayedMonth(){
                console.log(regex.exec(this.period));
                return "MOIS";
                /*if( this.selectedMonth == null )
                    return "MOIS";
                return this.months[this.selectedMonth];
                */
            },

            displayedYear(){
                return "ANNÉE";
            },

            selectableYear(){
                let middleYear = (new Date()).getFullYear();
                if( this.selectedYear ){
                    middleYear = this.selectedYear;
                }
                return this.yearAround(middleYear);
            },

            selectableMonths(){
                return this.months;
            }
        },

        methods: {
            handlerToggleSelector(){
                console.log('TOTO');
               this.showMonth = !this.showMonth;
               this.showYear = false;
            },

            handlerSelectYear(year){
                this.selectedYear = year;
                this.showMonth = false;
                this.showYear = true;
            },

            handlerSelectMonth(month){
                this.selectedMonth = month;
                this.showYear = false;
                // this.period = (this.selectedMonth && this.selectedYear) ? this.selectedYear+'-'+this.selectedMonth : null;
                this.trigger('change', this.period);
            },

            yearAround(year){
                console.log("Affichage des années autour de ", year);
                let years = [];
                for( let i = year-6; i < year+6; i++ ){
                    years.push(i);
                }
                console.log(years);
                return years;
            }
        },

        mounted(){
            if( window )
                window.addEventListener('click', e => {
                    this.showMonth = this.showYear = false;
                })
        }
    }
</script>