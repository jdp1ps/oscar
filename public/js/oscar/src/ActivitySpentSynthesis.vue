<template>
    <section>
        <transition name="fade">
            <div class="overlay" v-if="showCuration">
                <div class="overlay-content">
                    <h3>
                        Qualification des comptes
                        <span class="overlay-closer" @click="showCuration = false">X</span>
                    </h3>
                    <p>Les comptes suivants ne sont pas qualifiés, vous pouvez utiliser cet écran pour les attribuer à une masse budgétaire :</p>
                    <div class="card row" v-for="c in synthesis.curations">
                        <div class="col-md-4">
                            <strong>{{ c.compte }}</strong> - <em>{{ c.compteInfos.label }}</em>
                        </div>
                        <div class="col-md-8">
                            <select name="" id="" class="form-control" v-model="affectations[c.compte]" @change="updateAffectations(c.compte, $event)">
                                <option value="0">Ignorer</option>
                                <option value="1">Traiter comme une recette</option>
                                <option :value="masse" v-for="text,masse in masses">{{ text }}</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <button @click="handlerCurationCancel" class="btn btn-danger"><i class="icon-cancel-circled"></i>Annuler</button>
                    <button @click="handlerCurationConfirm" class="btn btn-success"><i class="icon-floppy"></i>Enregistrer</button>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="alert alert-danger" v-if="error">
                <i class="icon-attention-1"></i>
                Il y'a eut un problème lors de la récupération des données financières :
                {{ error }}
            </div>
        </transition>

        <transition name="fade">
            <div class="alert-warning alert" v-if="warning">
                <i class="icon-warning-empty"></i>
                Les données affichées peuvent ne pas être à jour :
                {{ warning }}
            </div>
        </transition>

        <transition name="fade">
            <div class="pending" v-if="pendingMsg">
                <div class="">
                    <i class="icon-spinner animate-spin"></i>
                    {{ pendingMsg }}
                </div>
            </div>
        </transition>

        <table class="table table-condensed" v-if="!pendingMsg">
            <tr v-for="m,k in masses">
                <th>{{ m }}</th>
                <td style="text-align: right; white-space: nowrap">{{ synthesis[k] | money  }}&nbsp;€</td>
            </tr>
            <tr style="border-top: solid #000 thin" v-if="synthesis['N.B']">
                <th>
                    Hors masse<br>
                    <small style="font-weight: 300" class="error-block"><i class="icon-attention"></i> Les annexes de certains comptes ne sont pas renseignés :
                        <ul>
                            <li v-for="c in getNoMasse"><strong>{{c}}</strong>
                                <div v-if="synthesis.curations">

                                </div>
                            </li>
                        </ul>
                        <a @click="handlerCuration" v-if="manageDepense" class="btn btn-xs btn-default"> <i class="icon-cog"></i>Qualifer les comptes</a>
                        <span v-else>Merci de contacter un administrateur pour que les annexes des comptes soient configurés.</span>
                    </small>
                </th>
                <td style="text-align: right; white-space: nowrap">{{ synthesis['N.B'] | money  }}&nbsp;€</td>
            </tr>
            <tr style="border-top: solid #000 thin; font-size: 1.6em">
                <th>TOTAL : </th>
                <td style="text-align: right; white-space: nowrap">{{ synthesis['total'] | money }}&nbsp;€</td>
            </tr>
        </table>

        <table class="table table-condensed" v-if="synthesis.recettes">
            <tr>
                <th><i class="icon-euro"></i>Recettes</th>
                <td style="text-align: right; white-space: nowrap">{{ synthesis.recettes.total | money  }}&nbsp;€</td>
            </tr>
        </table>

        <small>Données mise à jour : <strong v-if="dateUpdated">{{ dateUpdated.date | dateFull }}</strong></small>
    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  ActivitySpentSynthesis --filename.js ActivitySpentSynthesis.js --dist public/js/oscar/dist public/js/oscar/src/ActivitySpentSynthesis.vue


    export default {
        props: ['url', 'manageDepense'],

        data() {
            return {
                error: null,
                warning: null,
                pendingMsg: "",
                synthesis: [],
                masses: {},
                dateUpdated: null,
                showCuration: false,
                affectations: {}
            }
        },

        computed: {
            getNoMasse(){
                let out = [];
                for( let m in this.synthesis.details['N.B'] ){
                    let labelCompte = this.synthesis.details['N.B'][m];
                    if( out.indexOf(labelCompte) < 0 )
                        out.push(labelCompte);
                }
                return out;
            }

        },

        methods: {
            ////////////////////////////////////////////////////////////////
            //
            // HANDLERS
            //
            ////////////////////////////////////////////////////////////////
            handlerCuration(){
                if( this.synthesis.curations ){
                    for( let c in this.synthesis.curations ){
                        if( !this.affectations.hasOwnProperty(c) ){
                            this.affectations[c] = "";
                        }
                    }
                }
                this.showCuration = true;
            },

            updateAffectations( compte, event ){
                console.log(compte, event.target.value);
                this.affectations[compte] = event.target.value;
                console.log(this.affectations);
            },

            handlerCurationCancel(){
                this.showCuration = false;
            },

            handlerCurationConfirm(){
                this.pendingMsg = "Enregistrement";
                this.$http.post(this.url, {'affectation': this.affectations }).then(
                    success => {
                        this.fetch();
                        this.showCuration = false;
                    },
                    error => {
                        if( error.status == 403 ){
                            this.error = "Vous n'avez pas l'autorisation d'accès à ces informations.";
                        } else {
                            this.error = "Impossible de charger les dépenses pour ce PFI : " + error.data
                        }
                    }
                ).then(n => { this.pendingMsg = ""; });
            },

            ////////////////////////////////////////////////////////////////
            //
            // OPERATIONS REST
            //
            ////////////////////////////////////////////////////////////////

            /**
             * Chargement des jalons depuis l'API
             */
            fetch() {
                this.pendingMsg = "Chargement des donnèes";

                this.$http.get(this.url).then(
                    success => {
                        this.synthesis = success.data.synthesis;
                        this.masses = success.data.masses;
                        this.dateUpdated = success.data.dateUpdated;
                        this.error = success.data.error;
                        this.warning = success.data.warning;
                    },
                    error => {
                        if( error.status == 403 ){
                            this.error = "Vous n'avez pas l'autorisation d'accès à ces informations.";
                        } else {
                            this.error = "Impossible de charger les dépenses pour ce PFI : " + error.data
                        }
                    }
                ).then(n => { this.pendingMsg = ""; });
            },
        },

        mounted() {
            this.fetch()
        }
    }
</script>