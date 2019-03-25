<template>
    <div>
        <h1>Vue gant</h1>
        <div id="vue" style="position: relative; padding: 1em; padding-top: 5em">

            <div v-for="a in activitiesDisplay" class="activity" style="" :style="{ 'left': a.left +'px', 'width': a.width+'px'}">
                <abbr title="">{{ a.acronym }}</abbr>
                <strong><i class="icon-cube"></i> {{ a.label }}</strong>
                <small>
                    du {{ a.dateStart }} au {{ a.dateEnd }} <br>
                    ( {{ a.duration }} mois)
                </small>
            </div>
            <div class="years">
                <article v-for="year in years" class="year" :style="{ 'width': (unit * 12)+'px'}">
                    <h2>{{ year }}</h2>
                    <div class="months">
                        <div class="month" v-for="m in months" :style="{ 'width': unit+'px' }">
                            {{ m }}
                        </div>
                    </div>
                </article>
            </div>
        </div>
        <pre>{{ dateBounds }}</pre>
        <pre>{{ $data }}</pre>
    </div>
</template>
<style lang="scss">
    .years {
        white-space: nowrap;
        position: absolute;
        z-index: 1;
        top: 0;
        height: 100%;
    }
    .months {
        height: 100%;
    }
    .month {
        display: inline-block;
        height: 100%;
            background: rgba(255,255,255,.1);
        &:nth-child(odd) {
            background: rgba(255,255,255,.2);
         }
    }
    .year {
        background: #0b93d5;
        border: white solid 1px;
        min-height: 150px;
        display: inline-block;
        height: 100%;
        h2 {
            margin: 0;
            padding: 0;
        }
    }

    .activity {
        background: rgba(255,255,255,.5);
        padding: .25em;
        margin: .25em;
        z-index: 5;
        position: relative;
        display: block;
    }
</style>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  ActivityGant --filename.js ActivityGant.js --dist public/js/oscar/dist public/js/oscar/src/ActivityGant.vue

    export default {
        props: {
            url: {
                required: true
            }
        },


        data(){
            return {
                activities: [],
                unit: 50,
                months: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec']
            };
        },

        computed: {
            activitiesDisplay(){

                let activities = [];

                this.activities.forEach(activity => {
                    if( !activity.dateStart || !activity.dateStart ){
                        console.log("Pas de date pour", activity)
                    } else {
                        let start = new Date(activity.dateStart)
                        let end = new Date(activity.dateEnd)

                        let left = ((start.getFullYear() - this.dateBounds.startYear) * 12 + start.getMonth()) * this.unit;


                        let monthStart = 12 - start.getMonth() + 1;
                        let monthEnd = (end.getFullYear()-start.getFullYear() - 1)*12 + end.getMonth();

                        //let monthDuration = (end.getFullYear() - start.getFullYear() -1) * 12
                        let width = (end.getTime() - start.getTime())



                        activities.push({
                            left: left,
                            label: activity.label,
                            acronym: activity.projectacronym,
                            start: activity.dateStart,
                            end: activity.dateEnd,
                            duration: monthStart + monthEnd,
                            width: (monthStart + monthEnd) * this.unit,
                            dateStart: activity.dateStart,
                            dateEnd: activity.dateEnd
                        })

                    }
                })

                return activities;
            },


            dateBounds(){
                let bounds = {};
                this.activities.forEach( activity => {
                    let end = new Date(activity.dateEnd);
                    let start = new Date(activity.dateStart);

                    if( !bounds.start || bounds.start > activity.dateStart ){
                        bounds.start = activity.dateStart;
                        bounds.startYear = start.getFullYear();
                        bounds.startMili = start.getTime();
                    }
                    if( !bounds.end || bounds.end < activity.dateEnd ){
                        bounds.end = activity.dateEnd;
                        bounds.endYear = end.getFullYear();
                        bounds.endMili = end.getTime();
                    }
                })
                return bounds;
            },

            years(){
                let years = [];
                for( let i = this.dateBounds.startYear; i<= this.dateBounds.endYear; i++ ){
                    years.push(i);
                }
                return years;
            }

        },


        methods: {
            fetch(){
                this.$http.get(this.url).then(
                    ok => {
                        console.log(ok.body)
                        this.activities = ok.body.datas.content
                    },
                    ko => {
                        console.error(ko)
                    }
                )
            }
        },

        mounted(){
            this.fetch();
        }
    }

</script>