<template>
    <div class="day-details" :class="{'locked': day.locked}">

        <h3 @click.stop.prevent="$emit('debug', day)">Déclarer des heures <strong>{{ label }}</strong></h3>

        <a href="#" @click.prevent="$emit('cancel')" class="link">
            <i class="icon-angle-left"></i> Retour
        </a>
        <div class="alert alert-danger" v-if="day.locked">
            {{ day.lockedReason }}
        </div>
        <div v-else>
            Compléter avec :
            <wpselector :workpackages="workPackages" @select="addToWorkpackage" :selection="selection"></wpselector>
        </div>

        <section>

            <template v-if="day.declarations.length">
                <h3><i class="icon-archive"></i> Heures identifiées sur des lots</h3>

                <day :d="d" v-for="d in day.declarations" :key="d.id"
                    @debug="$emit('debug', $event)"
                    @removetimesheet="$emit('removetimesheet', $event)"
                    @edittimesheet="$emit('edittimesheet', $event, day)"
                ></day>

                <article class="wp-duration card xs">
                    <span class="text-large text-xl">Total<br>
                        <small class="text-thin text-small">Sur les activités soumises aux déclarations</small>
                    </span>
                    <div class="total">
                        <span class="text-large text-xl">{{ totalWP | heures }}</span>
                        <em>heure(s)</em>
                    </div>
                    <div class="left"></div>
                </article>
                <hr>
            </template>


            <article class="wp-duration card xs" v-for="t in day.teaching">
                <strong>
                    <i class="icon-teaching"></i> Enseignements<br>
                    <small>{{ t.description }}</small>
                </strong>
                <div class="total">{{ t.duration | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash" @click="$emit('removetimesheet', t)"></i>
                </div>
            </article>

            <article class="wp-duration card xs" v-for="t in day.training">
                <strong>
                    <i class="icon-training"></i> Formation<br>
                    <small>{{ t.description }}</small>
                </strong>
                <div class="total">{{ t.duration | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash" @click="$emit('removetimesheet', t)"></i>
                </div>
            </article>

            <article class="wp-duration card xs" v-for="t in day.vacations">
                <strong>
                    <i class="icon-vacation"></i> Congès<br>
                    <small>{{ t.description }}</small>
                </strong>
                <div class="total">{{ t.duration | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash" @click="$emit('removetimesheet', t)"></i>
                </div>
            </article>

            <article class="wp-duration card xs" v-for="t in day.sickleave">
                <strong>
                    <i class="icon-sickleave"></i> Arrêt maladie<br>
                    <small>{{ t.description }}</small>
                </strong>
                <div class="total">{{ t.duration | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash" @click="$emit('removetimesheet', t)"></i>
                </div>
            </article>

            <article class="wp-duration card xs" v-for="t in day.research">
                <strong>
                    <i class="icon-research"></i> Autre projet de recherche<br>
                    <small>{{ t.description }}</small>
                </strong>
                <div class="total">{{ t.duration | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash" @click="$emit('removetimesheet', t)"></i>
                </div>
            </article>

            <article class="wp-duration card xs" v-for="t in day.absent">
                <strong>
                    <i class="icon-abs"></i> Absence<br>
                    <small>{{ t.description }}</small>
                </strong>
                <div class="total">{{ t.duration | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash" @click="$emit('removetimesheet', t)"></i>
                </div>
            </article>

            <article class="wp-duration card xs" v-for="t in day.infos">
                <strong>
                    <i class="icon-infos"></i> Infos<br>
                    <small>{{ t.description }}</small>
                </strong>
                <div class="total">{{ t.duration | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash" @click="$emit('removetimesheet', t)"></i>
                </div>
            </article>
            <hr>

            <article class="wp-duration card xs">
                <span  class="text-large text-xl">Total journée</span>
                <div class="total">
                    <span class="text-large text-xl">{{ day.duration | heures }}</span>
                    <em>heure(s)</em>
                </div>
                <div class="left">
                    &nbsp;
                </div>
            </article>


            <div class="alert-danger alert" v-if="day.duration > day.maxDay">
                <i class="icon-attention-circled"></i>
                Attention, le cumul des heures déclarées exéde la limite légale de <strong>{{ day.maxDay | heures }} heures</strong> fixée par le droit du travail.
            </div>
        </section>
        <a href="#" @click="debug = !debug"><i class="icon-bug"></i> debug</a>
        <pre v-show="debug">{{ day }}</pre>
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
            }
        }
    }
</script>