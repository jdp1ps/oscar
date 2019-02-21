<template>
    <div class="day-details" :class="{'locked': day.locked}">

        <h3 @click.stop.prevent.shift="$emit('debug', day)" class="title-with-menu">
            <div class="text"><i class="icon-calendar"></i> <strong>{{ label }}</strong></div>
            <nav class="right-menu">
                <a href="#" @click.prevent="$emit('copy', day)" v-show="day.othersWP || day.declarations.length" title="Copier les créneaux" >
                    <i class="icon-docs"></i>
                </a>

                <a href="#" @click.prevent="$emit('paste', day)" v-show="copiable">
                    <i class="icon-paste"></i>
                </a>
            </nav>
        </h3>

        <a href="#" @click.prevent="$emit('cancel')" class="btn btn-xs btn-default">
            <i class="icon-angle-left"></i> Retour
        </a>


        <div class="alert alert-danger" v-show="day.locked">
            <i class="icon-attention"></i> Cette journée est verrouillé <strong>{{ day.lockedReason }}</strong>
        </div>

        <div class="alert alert-danger" v-show="day.total > day.maxLength">
            <i class="icon-attention"></i> Le temps déclaré <strong>excède la durée autorisée</strong>. Vous ne pourrez pas soumettre votre feuille de temps.
        </div>

        <div>
            Compléter avec :
            <wpselector :others="others" :workpackages="workPackages" @select="addToWorkpackage" :selection="selection"></wpselector>
        </div>

        <section>

            <template v-if="day.declarations.length">
                <h3><i class="icon-archive"></i> Heures identifiées sur des lots</h3>

                <day :d="d" v-for="d in day.declarations" :key="d.id" :day-length="day.dayLength"
                    @debug="$emit('debug', $event)"
                    @removetimesheet="$emit('removetimesheet', $event)"
                    @edittimesheet="$emit('edittimesheet', $event, day)"
                ></day>

                <article class="wp-duration card xs">
                    <span>
                        <strong>
                            <i class="icon-archive"></i> Total<br>
                            <small>sur les lot de travail</small>
                        </strong>
                    </span>
                    <div class="total">
                        <span class="text-large text-xl">{{ totalWP | duration2(day.dayLength) }}</span>
                    </div>
                    <div class="left"></div>
                </article>
                <hr>
            </template>

            <section class="othersWP" v-if="day.othersWP">
                <h3><i class="icon-tags"></i> Heures Hors-Lots</h3>
                <article class="wp-duration card xs"  v-for="t in day.othersWP">
                    <strong>
                        <i :class="'icon-'+t.code"></i> {{ others[t.label] ? others[t.label].label : t.label  }}<br>
                        <small>{{ t.description }}</small>
                    </strong>
                    <div class="total">{{ t.duration | duration2(day.dayLength) }}</div>
                    <div class="left">
                        <i class="icon-trash" @click="$emit('removetimesheet', t)"></i>
                    </div>
                </article>
                <hr>
            </section>



            <article class="wp-duration card xl">
                <strong>
                    Total de la journée<br>
                </strong>
                <div class="total">{{ totalJour | duration2(day.dayLength) }}</div>
            </article>
        </section>
    </div>
</template>

<style lang="scss">

        .wp-duration {
            display: flex;
            border-bottom: thin solid white;
            align-items: center;
            strong small {
                font-weight: 100;
            }
            &[class*='status-'] {
                border-left: solid 4px #ddd;
            }
            &.status-5 {
                border-left-color: #0b58a2;
            }
            .icon-comment {
                color: #CCC;
                &.with-comment { color: #000 }
            }
        }
        .left {
            flex: 0 0 35px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding-left: 8px;
            border-left: solid #fff thin;
        }
        .total {
            margin-left: auto;
            font-size: 1.4em;
            font-weight: 700;
            padding-right: .5em;
            em {
                font-size: .5em;
                font-weight: 100;
                line-height: 2em;
            }
        }
        [class*="icon-"]{
            text-align: center;
        }
        .icon-trash {
            cursor: pointer;
            text-align: center;
            &:hover {
                background: #0b58a2;
                color: white;
            }
        }

</style>

<script>
    import TimesheetMonthWorkPackageSelector from './TimesheetMonthWorkPackageSelector.vue';
    import TimesheetMonthDeclarationItem from './TimesheetMonthDeclarationItem.vue';

    export default {
        name: 'TimesheetMonthDayDetails',

        components: {
            wpselector: TimesheetMonthWorkPackageSelector,
            day: TimesheetMonthDeclarationItem
        },

        props: {
            workPackages: {
                require: true
            },
            others: {
                require: true
            },
            day: {
               require: true
            },
            selection: {
                require: true
            },
            label: {
                require: true
            },
            dayExcess: {
                require: true
            },
            copiable: {
                default: null
            }
        },

        data(){
            return {
                formAdd: false,
                debug: false
            }
        },

        filters: {
            heures(v){
                let heures = Math.floor(v);
                let minutes = Math.round((v - heures)*60);
                if( minutes < 10 ) minutes = '0'+minutes;
                console.log(v, ' => ',heures,'h',minutes);
                return heures+":"+minutes;
            }
        },

        computed: {

            isExceed(){
                return this.total > this.day.dayLength;
            },

            totalWP(){
                let t = 0.0;
                this.day.declarations.forEach( d => {
                    t += d.duration;
                })
                return t;
            },

            totalHWP(){
                let t = 0.0;
                if( this.day.othersWP ) {
                    this.day.othersWP.forEach(d => {
                        t += d.duration;
                    })
                }
                return t;

            },

            totalJour(){
              return this.totalWP + this.totalHWP;

            },

            enseignements(){
                let t = 0.0;
                this.day.teaching.forEach( ts => {
                    t += ts.duration;
                })
                return t;
            },
            abs(){
                let t = 0.0;
                this.day.vacations.forEach( ts => {
                    t += ts.duration;
                })
                return t;
            },
            learn(){
                let t = 0.0;
                this.day.training.forEach( ts => {
                    t += ts.duration;
                })
                return t;
            },
            other(){
                let t= 0.0;
                this.day.infos.forEach( ts => {
                   t += ts.duration
                });
                return t;
            },
            sickleave(){
                let t= 0.0;
                this.day.sickleave.forEach( ts => {
                    t += ts.sickleave
                });
                return t;
            },
            research(){
                let t= 0.0;
                this.day.research.forEach( ts => {
                    t += ts.research
                });
                return t;
            }
        },

        methods: {
            addToWorkpackage( wp){
                this.$emit('addtowp', wp);
            },

            hasDeclarationHWP(code){
                console.log(code, this.day[code], this.day);
                return this.day[code] && this.day[code].length;
            }
        }
    }
</script>