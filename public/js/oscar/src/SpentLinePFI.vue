<template>
    <section class="spentlines">

        <transition name="fade">
            <div class="error overlay" v-if="error">
                <div class="overlay-content">
                    <i class="icon-warning-empty"></i>
                    {{ error }}
                    <br>
                    <a href="#" @click="error = null" class="btn btn-sm btn-default btn-xs">
                        <i class="icon-cancel-circled"></i>
                        Fermer</a>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="pending overlay" v-if="pendingMsg">
                <div class="overlay-content">
                    <i class="icon-spinner animate-spin"></i>
                    {{ pendingMsg }}
                </div>
            </div>
        </transition>

        <div class="overlay" v-if="editCompte">
            <div class="overlay-content">
                <h3><i class="icon-zoom-in-outline"></i>Modification de la masse : {{ editCompte.code }} - {{ editCompte.label }}</h3>
                <hr>
                <select name="" v-model="editCompte.annexe">
                    <option value="0">Ignoré</option>
                    <option value="1">Recette</option>
                    <option :value="m" v-for="masse,m in spentlines.masses">{{ masse }}</option>
                </select>

                <button class="btn btn-danger" @click="editCompte = null"><i class="icon-cancel-circled-outline"></i>Annuler</button>
                <button class="btn btn-success" @click="handlerAffectationCompte(editCompte)"><i class="icon-valid"></i>Valider</button>
            </div>
        </div>

        <div class="overlay" v-if="details">
            <div class="overlay-content">
                <h3><i class="icon-zoom-in-outline"></i>Détails des entrées comptables</h3>
                <button class="btn btn-default" @click="details = null">Fermer</button>

                <table class="list table table-condensed table-bordered table-condensed card">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Compte Budgetaire</th>
                        <th>Centre de profit</th>
                        <th>Compte général</th>
                        <th>Masse</th>
                        <th>Date comptable</th>
                        <th>Date paiement</th>
                        <th>Année</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="text-small" v-for="d in details.details">
                        <td>{{ d.syncid }}</td>
                        <td>{{ d.texteFacture|d.designation }}</td>
                        <td style="text-align: right">{{ d.montant | money }}</td>
                        <td>{{ d.compteBudgetaire }}</td>
                        <td>{{ d.centreFinancier }}</td>
                        <td><strong>{{ d.compteGeneral }}</strong> : {{ d.type }}</td>
                        <td><strong>{{ d.masse }}</strong></td>
                        <td>{{ d.dateComptable }}</td>
                        <td>{{ d.datePaiement }}</td>
                        <td>{{ d.dateAnneeExercice }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="container-fluid">

            <div class="row">
                <div class="col-md-3">
                    <h3>
                        <i class="icon-help-circled"></i>
                        Informations
                    </h3>
                    <div class="card">
                        <table class="table table-condensed card synthesis" v-if="spentlines">
                            <tbody>
                                <tr>
                                    <th><small>PFI</small></th>
                                    <td style="text-align: right">
                                        {{ informations.PFI }}
                                    </td>
                                </tr>
                                <tr>
                                    <th><small>N°OSCAR</small></th>
                                    <td style="text-align: right">
                                        {{ informations.numOscar }}
                                    </td>
                                </tr>
                                <tr>
                                    <th><small>Montant</small></th>
                                    <td style="text-align: right">
                                        {{ informations.amount | money }}
                                    </td>
                                </tr>
                                <tr>
                                    <th><small>Projet</small></th>
                                    <td style="text-align: right">
                                        <strong>{{ informations.projectacronym }}</strong><br>
                                        <small>{{ informations.project }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th><small>Activité</small></th>
                                    <td style="text-align: right">
                                        <small>{{ informations.label }}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <a :href="urlActivity" v-if="urlActivity" class="btn btn-default btn-xs"><i class="icon-cube"></i> Revenir à l'activité</a>
                        <form :action="urlSync" method="post" class="form-inline" v-if="urlSync">
                            <input type="hidden" name="action" value="update" />
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="icon-signal"></i>
                                Mettre à jour les données depuis SIFAC
                            </button>
                        </form>
                        <a :href="urlDownload" class="btn btn-default btn-xs" v-if="urlDownload">
                            <i class="icon-download"></i>
                            Télécharger les données (Excel)</a>
                    </div>


                    <h3><i class="icon-calculator"></i>Dépenses</h3>
                    <table class="table table-condensed card synthesis" v-if="spentlines">
                        <tbody>
                        <tr v-for="dt,key in spentlines.masses">
                            <th>
                                <small>{{ dt }}</small>
                                <a class="label label-info xs" :href="'#repport-' + key">{{ spentlines.synthesis[key].nbr}}</a>
                            </th>
                            <td style="text-align: right">{{ spentlines.synthesis[key].total | money}}</td>
                        </tr>
                        <tr v-if="spentlines.synthesis['N.B'].total != 0">
                            <th><small><i class="icon-attention"></i> Hors-masse</small><span class="label label-info">{{ spentlines.synthesis['N.B'].nbr}}</span></th>
                            <td style="text-align: right">{{ spentlines.synthesis['N.B'].total | money}}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr class="total">
                            <th>Total</th>
                            <td style="text-align: right">{{ totalDepenses | money}}</td>
                        </tr>
                        </tfoot>
                    </table>

                    <h3><i class="icon-calculator"></i>Recettes</h3>
                    <table class="table table-condensed card synthesis" v-if="spentlines">
                        <tbody>
                        <tr>
                            <th>Recette <a class="label label-info xs" href="#repport-1">{{ spentlines.synthesis['1'].nbr}}</a></th>
                            <td style="text-align: right">{{ spentlines.synthesis['1'].total | money}}</td>
                        </tr>
                        </tbody>
                    </table>

                    <a href="#" @click.prevent="displayIgnored = !displayIgnored">
                        <span v-if="displayIgnored"><i class="icon-eye-off"></i> Cacher</span>
                        <span v-else><i class="icon-eye"></i> Montrer</span>
                        les données ignorées
                    </a>
                    <table class="table table-condensed card synthesis" v-if="spentlines && displayIgnored">
                        <tbody>
                        <tr>
                            <th>
                                Ignorées
                                <a class="label label-info"  href="#repport-0">{{ spentlines.synthesis['0'].nbr}}</a>
                            </th>
                            <td style="text-align: right">{{ spentlines.synthesis['0'].total | money}}</td>
                        </tr>
                        </tbody>
                    </table>


                </div>
                <div class="col-md-9" style="height: 80vh; overflow-y: scroll">

                    <div v-if="spentlines != null">
                        <div v-for="m, k in masses">
                            <h3 :id="'repport-' + k">{{ m }}</h3>
                            <spent-line-p-f-i-grouped
                                :lines="byMasse.datas[k]" :total="spentlines.synthesis[k].total"
                                @editcompte="handlerEditCompte"
                                @detailsline="handlerDetailsLine"
                            />
                        </div>

                        <div v-if="Object.keys(byMasse.datas['N.B']).length > 0">
                            <h3 :id="'repport-nb'">Hors-masse</h3>
                            <div class="alert alert-warning">
                                <i class="icon-attention"></i> Les comptes des entrées suivantes ne sont pas qualifié.
                            </div>
                            <spent-line-p-f-i-grouped
                                    :lines="byMasse.datas['N.B']" :total="spentlines.synthesis['N.B'].total"
                                    @editcompte="handlerEditCompte"
                                    @detailsline="handlerDetailsLine"
                            />
                        </div>

                        <div v-if="Object.keys(byMasse.datas['recettes']).length > 0">
                            <h3 :id="'repport-1'">Recettes</h3>
                            <spent-line-p-f-i-grouped
                                    :lines="byMasse.datas['recettes']" :total="spentlines.synthesis['1'].total"
                                    @editcompte="handlerEditCompte"
                                    @detailsline="handlerDetailsLine"
                            />
                        </div>
                        <div v-if="Object.keys(byMasse.datas['ignorés']).length > 0">
                            <h3 :id="'repport-0'">Ignorés</h3>
                            <spent-line-p-f-i-grouped
                                    :lines="byMasse.datas['ignorés']" :total="spentlines.synthesis['0'].total"
                                    @editcompte="handlerEditCompte"
                                    @detailsline="handlerDetailsLine"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  SpentLinePFI --filename.js SpentLinePFI.js --dist public/js/oscar/dist public/js/oscar/src/SpentLinePFI.vue

    import SpentLinePFIGrouped from "./SpentLinePFIGrouped";

    export default {
        props: [
            'moment',
            'url',
            'masses',
            'urlSpentAffectation',
            'informations',
            'urlActivity',
            'urlSync',
            'urlDownload'
        ],

        components: {
            SpentLinePFIGrouped
        },

        data() {
            return {
                state: "masse",
                error: null,
                pendingMsg: "",
                spentlines: null,
                details: null,
                displayIgnored: false,
                editCompte: null
            }
        },

        computed: {
            totalDepenses() {
                let total = 0.0;
                for (let i in this.spentlines.synthesis) {
                    if (i != '0' && i != '1') {
                        total += this.spentlines.synthesis[i].total;
                    }
                }
                return total;
            },

            byMasse() {
                let out = {
                    datas: {
                        'N.B': {},
                        'recettes': {},
                        'ignorés': {}
                    },
                    totaux: {
                        'N.B': 0.0,
                        'recettes': 0.0,
                        'ignorés': 0.0
                    }
                };

                for (let k in this.masses) {
                    out.datas[k] = {};
                    out.totaux[k] = 0.0;
                }

                if( this.spentlines ) {
                    for (let s in this.spentlines.spents) {
                        let line = this.spentlines.spents[s];
                        let masse = line.masse;
                        if( masse == '1' ) masse = 'recettes';
                        if( masse == '0' ) masse = 'ignorés';
                        let numPiece = line.numPiece;
                        console.log(numPiece, line);

                        if( !out.datas.hasOwnProperty(masse) ){
                            console.log(masse, "non trouvée");
                            masse = 'N.B';
                        }
                        if( !out.datas[masse].hasOwnProperty(numPiece) ){
                            out.datas[masse][numPiece] = {
                                'ids': [],
                                'numpiece': numPiece,
                                'text': [],
                                'types': [],
                                'montant': 0.0,
                                'compteBudgetaires': [],
                                'comptes': [],
                                'masse': [],
                                'dateComptable': line.dateComptable,
                                'datePaiement': line.datePaiement,
                                'annee': line.dateAnneeExercice,
                                'refPiece': line.refPiece,
                                details: []
                            };
                        }
                        out.datas[masse][numPiece].details.push(line);

                        let text = line.texteFacture;
                        let designation = line.designation;
                        let type = line.type;
                        let compte = line.compteGeneral;
                        let compteBudgetaire = line.compteBudgetaire;

                        out.datas[masse][numPiece].montant += line.montant;

                        if( text && out.datas[masse][numPiece].text.indexOf(text) < 0 ){
                            out.datas[masse][numPiece].text.push(text);
                        }

                        if( designation && out.datas[masse][numPiece].text.indexOf(designation) < 0 ){
                            out.datas[masse][numPiece].text.push(designation);
                        }

                        if( type && out.datas[masse][numPiece].types.indexOf(type) < 0 ){
                            out.datas[masse][numPiece].types.push(type);
                        }

                        if( compte && out.datas[masse][numPiece].comptes.indexOf(compte) < 0 ){
                            out.datas[masse][numPiece].comptes.push(compte);
                        }

                        if( compteBudgetaire && out.datas[masse][numPiece].compteBudgetaires.indexOf(compteBudgetaire) < 0 ){
                            out.datas[masse][numPiece].compteBudgetaires.push(compteBudgetaire);
                        }
                    }
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
            handlerEditCompte(compte){
                this.editCompte = JSON.parse(JSON.stringify(this.spentlines.comptes[compte]));
            },

            handlerDetailsLine(line){
                this.details = line;
            },

            handlerAffectationCompte(compte){
                //$codeCompteFull => $compteAffectation
                let affectations = {};
                affectations[compte.codeFull] = compte.annexe;
                this.editCompte = null;
                this.pendingMsg = "Modification de la masse pour " + compte.codeFull;


                this.$http.post(this.urlSpentAffectation, {'affectation': affectations }).then(
                    success => {
                        this.editCompte = null;
                        this.fetch();
                    },
                    error => {
                        if( error.status == 403 ){
                            this.error = "Vous n'avez pas l'autorisation d'accès à ces informations.";
                        } else {
                            this.error = error.data
                        }
                        this.pendingMsg = "";
                    }
                );
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
                this.pendingMsg = "Chargement des dépense";

                this.$http.get(this.url).then(
                    success => {
                        this.spentlines = success.data.spents;
                    },
                    error => {
                        if (error.status == 403) {
                            this.error = "Vous n'avez pas l'autorisation d'accès à ces informations.";
                        } else {
                            this.error = "Impossible de charger les dépenses pour ce PFI : " + error.data
                        }
                    }
                ).then(n => {
                    this.pendingMsg = "";
                });
            },
        },

        mounted() {
            this.fetch()
        }
    }
</script>