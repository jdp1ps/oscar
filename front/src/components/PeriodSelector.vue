<template>
    <span class="period-selector" :class="{ 'open': showYear || showMonth }" @mouseleave="handlerMouseLeave">

        <span class="visualizer" @click.prevent.stop="handlerToggleSelector">
            <i class="icon-calendar"></i>
            <span>
                {{ displayedValue }}
            </span>
            <i class="icon-angle-down"></i>
        </span>


        <span class="selector selector-year" v-if="showYear">
            <span class="scroll-left scroll" @click.prevent.stop="setDefileYear(-1)">
                <i class="icon-angle-left"></i>
            </span>
            <span class="selector-content">
                <span v-for="y in selectableYear" @click.prevent.stop="handlerSelectYear(y)" :class="{ 'selected': y == selectedYear, 'disabled': y > maxYear || y < minYear }">
                    {{ y }}
                </span>
                <nav class="selector-options">
                    <a class="selector-option" @click="cleanUp">Vider</a>
                    <a class="selector-option" @click="today">Période en cours</a>
                </nav>
            </span>
            <span class="scroll-right scroll" @click.prevent.stop="setDefileYear(1)">
                <i class="icon-angle-right"></i>
            </span>
        </span>

        <span class="selector selector-month" v-if="showMonth">
            <span class="selector-content">
                <span v-for="m,i in selectableMonths" @click.prevent.stop="handlerSelectMonth(i)" :class="{ 'selected': i == selectedMonth-1, 'disabled': i > maxMonth || i < minMonth  }">
                    {{ m }}
                </span>
                <nav class="selector-options">
                    <a class="selector-option" @click="cleanUp">Vider</a>
                    <a class="selector-option" @click="today">Période en cours</a>
                </nav>
            </span>
        </span>
    </span>
</template>

<script>

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
              showYear: false,
              defileYear : null
          }
        },

        props: {
            value: { default: ""},
            months: { default: function(){ return ["Janvier", 'Février', "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"] } },
            period: { default: null, required: true },
            max: { default: null },
            min: { default: null },
        },

        computed: {
            displayedMonth(){
                if( !this.period ){
                    return "MOIS";
                } else {
                    let extract = this.period.match(regex);
                    let indexMois =  extract.length == 3 ? parseInt(extract[2])-1 : "INVALID";
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

            displayedValue(){
                if( !this.period ){
                    return "Indéfini";
                } else {
                    let extract = this.period.match(regex);
                    if( extract.length == 3 ){
                        return this.months[parseInt(extract[2])-1] + " " + extract[1];
                    } else {
                        return "INVALID!";
                    }
                }
            },

            selectableYear(){
                let middleYear = (new Date()).getFullYear();
                if( this.defileYear ){
                    middleYear = this.defileYear;
                } else if( this.selectedYear ){
                    middleYear = this.selectedYear;
                }
                return this.yearAround(middleYear);
            },

            maxYear(){
                if( this.max ){
                    let extract = this.max.match(regex);
                    if( extract.length != 3 ) return Number.MAX_SAFE_INTEGER;
                    let year = parseInt(extract[1]);
                    let month = parseInt(extract[2]);
                    if( month == 1 ){
                        year -= 1;
                    }

                    return year;
                }
                return Number.MAX_SAFE_INTEGER;
            },

            maxMonth(){
                if( this.max ){
                    if( this.selectedYear == this.maxYear ){
                        let extract = this.max.match(regex);
                        let year = extract[1];
                        let month = extract[2]-1;
                        if( this.maxYear+1 == year ){
                           return 11;
                        }
                        return extract.length == 3 ? month : 11;
                    }
                }
                return 13;
            },

            minYear(){
                if( this.min ){
                    let extract = this.min.match(regex);
                    let year = parseInt(extract[1]);
                    let month = parseInt(extract[2]);
                    // if( month == 12 ){
                    //     year += 1;
                    // }
                    return year;
                }
                return Number.MIN_SAFE_INTEGER;
            },

            minMonth(){
                if( this.min ){
                    let extract = this.min.match(regex);
                    let year = parseInt(extract[1]);
                    let month = parseInt(extract[2])-1;
                    if( this.selectedYear > year ) return -1;
                    return month;
                }
                return -1;
            },

            selectableMonths(){
                return this.months;
            }
        },

        methods: {
            handlerMouseLeave(){
                this.hideSelector();
            },

            handlerToggleSelector(){
               this.showYear = !this.showYear;
               this.showMonth = false;
            },

            handlerSelectYear(year){
                console.log('Year selected', year);
                this.selectedYear = year;
                this.showMonth = true;
                this.showYear = false;
            },

            handlerSelectMonth(month){
                console.log('Month selected', month);
                this.selectedMonth = month + 1; // Up (début d'index à 0)
                let value = null;
                if( this.selectedMonth && this.selectedYear ){
                    let year = this.selectedYear;
                    let month = this.selectedMonth < 10 ? '0'+this.selectedMonth : this.selectedMonth;
                    value = year+'-'+month;

                }
                this.hideSelector();
                this.$emit('change', value);
            },

            hideSelector(){
                this.showYear = false;
                this.showMonth = false;
            },

            cleanUp(){
              this.$emit('change', "");
            },

            today(){
                let now = new Date();
                this.$emit('change', now.getFullYear()+'-'+now.getMonth());
            },

            setDefileYear(direction){
                var base;
                if( direction == 1 ){
                    base = this.selectableYear[this.selectableYear.length-1];
                    base += this.selectableYear.length/2;
                } else {
                    base = this.selectableYear[0];
                    base -= this.selectableYear.length/2;
                }
                this.defileYear = base;

            },

            yearAround(year){
                let years = [];
                for( let i = year-6; i < year+6; i++ ){
                    years.push(i);
                }
                return years;
            }
        },

        mounted(){
            if( window )
                window.addEventListener('click', e => {
                    this.hideSelector();
                })
        }
    }
</script>