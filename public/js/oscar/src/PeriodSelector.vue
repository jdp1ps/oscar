<template>
    <span class="period-selector" :class="{ 'open': showYear || showMonth }" @mouseleave="handlerMouseLeave">
        <span class="visualizer" @click.prevent.stop="handlerToggleSelector">
            <i class="icon-calendar"></i>
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
        line-height: 1.6em;
        display: inline-flex;
        flex-align: center;
justify-content: center;    }

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
            period: { default: null, required: true }
        },

        computed: {
            displayedMonth(){
                if( !this.period ){
                    return "MOIS";
                } else {
                    let extract = this.period.match(regex);
                    let indexMois =  extract.length == 3 ? parseInt(extract[2])-1 : "INVALID";
                    console.log(indexMois);
                    return extract.length == 3 ? this.months[parseInt(extract[2])-1] : "INVALID";
                }
            },

            displayedYear(){
                if( !this.period ){
                    return "ANNÉE";
                } else {
                    let extract = this.period.match(regex);
                    return extract.length == 3 ? extract[1] : "INVALID";
                }
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
            handlerMouseLeave(){
                console.log("MouseLeave");
                this.hideSelector();
            },

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
                this.selectedMonth = month + 1;
                let periodEmit = (this.selectedMonth && this.selectedYear) ? this.selectedYear+'-'+this.selectedMonth : null;
                this.hideSelector();
                this.$emit('change', periodEmit);
            },

            hideSelector(){
                this.showYear = false;
                this.showMonth = false;
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