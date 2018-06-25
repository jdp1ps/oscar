<template>
    <div class="day-details" :class="{'locked': day.locked}">

        <h2>Déclarer des heures</h2>
        <div class="alert alert-danger" v-if="day.locked">
            {{ day.lockedReason }}
        </div>
        <div v-else>
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <i class="icon-plus-circled"></i>
                    Déclarer des heures
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                    <li v-for="wp in workPackages">
                        <a href="#" @click.prevent="addToWorkpackage(wp)"><abbr :title="wp.activity">[{{wp.acronym}}]</abbr>
                            <i class="icon-angle-right"></i>
                            <strong>{{wp.code}}</strong> <em>{{ wp.label }}</em><br/>
                            <small class="text-light">{{ wp.activity }}</small>
                        </a>
                    </li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#">Je suis en congès</a></li>
                    <li><a href="#">Je suis en formation</a></li>
                    <li><a href="#">J'ai donné des enseignements</a></li>
                </ul>
            </div>
            <div v-if="formAdd">
                <div class="">
                    <input type="text" class="form-control lg" v-model="addHours" placeholder="Indiquez les heures réalisées"/>
                </div>
            </div>
        </div>

        <section>
            <h3>
                <i class="icon-archive"></i>
                Heures identifiées sur des lots</h3>
            <article class="card wp-duration" v-for="d in day.declarations">
                <div class="infos">
                    <abbr :title="d.project">{{ d.acronym }}</abbr> <i class="icon-angle-right"></i> {{ d.wpCode }}<br>
                    <small><i class="icon-cubes"></i> {{ d.label }}</small>
                </div>

                <div class="total">
                    {{ d.duration | heures }}
                    <em>heure(s)</em>
                </div>

                <div class="left">
                    <i class="icon-trash"></i>
                    <i class="icon-pencil"></i>
                    <i class="icon-ok-circled"></i>
                </div>

            </article>

            <article class="wp-duration">
                <h3>
                    <i class="icon-graduation-cap"></i> Enseignements
                    <a href="#" @click="addDuration('teaching')"><i class=" icon-cw-outline"></i></a>
                </h3>
                <div class="total">{{ enseignements | heures }} <em>heure(s)</em></div>
                <div class="left">
                    &nbsp;
                </div>
            </article>

            <article class="wp-duration">
                <h3>
                    <i class="icon-leaf-1"></i> Congès <br> <small>Absence, congès, RTT</small>
                    <a href="#" @click="addDuration('vacation')"><i class=" icon-cw-outline"></i></a>
                </h3>
                <div class="total">{{ abs | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash"></i>
                    <i class="icon-pencil"></i>
                    <i class="icon-ok-circled"></i>
                </div>
            </article>

            <article class="wp-duration">
                <h3>
                    <i class="icon-lightbulb"></i> Formation <a href="#" @click="addDuration('learning')"><i class=" icon-cw-outline"></i></a><br>
                    <small>Période de formation</small>

                </h3>
                <div class="total">{{ learn | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash"></i>
                    <i class="icon-pencil"></i>
                    <i class="icon-ok-circled"></i>
                </div>
            </article>

            <article class="wp-duration">
                <h3><i class="icon-pin-outline"></i> Autre</h3>
                <div class="total">{{ other | heures }} <em>heure(s)</em></div>
                <div class="left">
                    <i class="icon-trash"></i>
                    <i class="icon-pencil"></i>
                    <i class="icon-ok-circled"></i>
                </div>
            </article>


            <article class="wp-duration">
                <span>total</span>
                <div class="total" :class="{ 'text-danger': isExceed}">
                    {{ total | heures }} / {{ day.dayLength }}
                    <em>heure(s)</em>
                </div>
                <div class="left">
                    &nbsp;
                </div>
            </article>
            <div class="alert-danger alert" v-if="total > day.dayLength">
                Attention, le cumul des heures déclarées exéde la limite retenue dans le cadre des projets soumis aux déclarations.
            </div>
        </section>
        <a href="#" @click="debug = !debug"><i class="icon-bug"></i> debug</a>
        <pre v-show="debug">{{ day }}</pre>
    </div>
</template>

<style lang="scss" scoped>
    .wp-duration {
        display: flex;
        border-bottom: thin solid white;
        align-items: center;
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
        font-size: 2em;
        font-weight: 700;
        padding-right: .5em;
        em {
            font-size: .5em;
            font-weight: 100;
            line-height: 2em;
        }
    }
</style>

<script>
    export default {
        props: {
            workPackages: {
                require: true
            },
            day: {
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
            total(){
                let t = this.enseignements + this.abs + this.learn + this.other + this.totalWP;
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
            }
        },

        methods: {
            addToWorkpackage(wp){
                this.formAdd = true;
//                console.log("AJOUT à ", wp);
            }
        }
    }
</script>