<template>
    <section class="spentlines">
        <h2><i class="icon-calculator"></i>Dépenses</h2>

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
                        <th>Date comptable</th>
                        <th>Date paiement</th>
                        <th>Année</th>
                    </tr>
                    </thead>
                    <tbody>
                <tr class="text-small" v-for="d in details.details">
                    <td>{{ d.syncid }}</td>
                    <td>{{ d.texteFacture|d.designation }}</td>
                    <td style="text-align: right">{{ d.montant.toFixed(2) }}</td>
                    <td>{{ d.compteBudgetaire }}</td>
                    <td>{{ d.centreFinancier }}</td>
                    <td><strong>{{ d.compteGeneral }}</strong> : {{ d.codeStr }}</td>
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
                <div class="col-md-2">
                    <div class="table table-condensed card">
                        <tbody>
                            <tr v-for="dt,key in byMasse.datas" v-if="key != 'recettes' && key != 'N.B'">
                                <th>{{masses[key]}}</th>
                                <td style="text-align: right">{{ byMasse.totaux[key].toFixed(2) }}</td>
                            </tr>
                            <tr>
                                <th>Hors-masse</th>
                                <td style="text-align: right">{{ byMasse.totaux['N.B'].toFixed(2) }}</td>
                            </tr>

                            <tr>
                                <th>Recettes</th>
                                <td style="text-align: right">{{ byMasse.totaux['recettes'].toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </div>

                </div>
                <div class="col-md-10">
                    <table class="list table table-condensed table-bordered table-condensed card" v-if="spentlines != null && state == 'pack'">
                        <thead>
                        <tr>
                            <th>N°</th>
                            <th>Ligne(s)</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Montant</th>
                            <th>Compte</th>
                            <th>Date comptable</th>
                            <th>Date paiement</th>
                            <th>Année</th>
                        </tr>
                        </thead>
                        <tbody v-for="l in spentlines">


                        <tr>
                            <td>{{ l.refPiece }}</td>
                            <td><button @click="details = l" class="btn btn-default">{{ l.details.length }}</button></td>
                            <td>{{ l.types ? l.types.join(',') : '' }}</td>
                            <td>{{ l.text.join(', ') }}</td>
                            <td style="text-align: right">{{ l.montant.toFixed(2) }}</td>
                            <td>{{ l.compteBudgetaire.join(', ') }}</td>
                            <td>{{ l.datecomptable }}</td>
                            <td>{{ l.datepaiement }}</td>
                            <td>{{ l.annee }}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div v-else-if="state == 'masse' && spentlines != null">

                        <!-- Données HORS-MASSE -->
                        <div v-if="byMasse.datas['N.B'].length">
                            <h2>Dépenses Hors-Masse</h2>
                            <table class="list table table-condensed table-bordered table-condensed card">
                                <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Ligne(s)</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th style="width: 8%">Montant</th>
                                    <th style="width: 8%">Compte</th>
                                    <th style="width: 8%">Date comptable</th>
                                    <th style="width: 8%">Date paiement</th>
                                    <th style="width: 8%">Année</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="l in byMasse.datas['N.B']">
                                    <td>{{ l.refPiece }}</td>
                                    <td><button @click="details = l" class="btn btn-default">{{ l.details.length }}</button></td>
                                    <td>{{ l.types ? l.types.join(',') : '' }}</td>
                                    <td>{{ l.text.join(', ') }}</td>
                                    <td style="text-align: right">{{ l.montant.toFixed(2) }}</td>
                                    <td>{{ l.compteBudgetaire.join(', ') }}</td>
                                    <td>{{ l.datecomptable }}</td>
                                    <td>{{ l.datepaiement }}</td>
                                    <td>{{ l.annee }}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr style="font-weight: bold; font-size: 1.2em">
                                    <td colspan="4" style="text-align: right">Total : </td>
                                    <td style="text-align: right">{{ byMasse.totaux['N.B'].toFixed(2) }}</td>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                </tfoot>
                            </table>
                            <div class="alert alert-info">
                                Les comptes des dépenses ci-dessus ne sont pas qualifiés sur les masses attendues.
                            </div>
                        </div>

                        <!-- MASSE DISPONIBLES -->
                        <div v-for="masse, k in masses">
                            <h2>{{ masse }}</h2>
                            <table class="list table table-condensed table-bordered table-condensed card" v-if="byMasse.datas[k].length > 0">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Ligne(s)</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th style="width: 8%">Montant</th>
                                        <th style="width: 8%">Compte</th>
                                        <th style="width: 8%">Date comptable</th>
                                        <th style="width: 8%">Date paiement</th>
                                        <th style="width: 8%">Année</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="l in byMasse.datas[k]">
                                        <td>{{ l.refPiece }}</td>
                                        <td><button @click="details = l" class="btn btn-default">{{ l.details.length }}</button></td>
                                        <td>{{ l.types ? l.types.join(',') : '' }}</td>
                                        <td>{{ l.text.join(', ') }}</td>
                                        <td style="text-align: right">{{ l.montant.toFixed(2) }}</td>
                                        <td>{{ l.compteBudgetaire.join(', ') }}</td>
                                        <td>{{ l.datecomptable }}</td>
                                        <td>{{ l.datepaiement }}</td>
                                        <td>{{ l.annee }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr style="font-weight: bold; font-size: 1.2em">
                                        <td colspan="4" style="text-align: right">Total : </td>
                                        <td style="text-align: right">{{ byMasse.totaux[k].toFixed(2) }}</td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div v-else class="alert alert-info">
                                Aucune entrée.
                            </div>
                        </div>

                        <!-- Données HORS-MASSE -->
                        <div v-if="byMasse.datas['recettes'].length">
                            <h2>Recettes</h2>
                            <table class="list table table-condensed table-bordered table-condensed card">
                                <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Ligne(s)</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th style="width: 8%">Montant</th>
                                    <th style="width: 8%">Compte</th>
                                    <th style="width: 8%">Date comptable</th>
                                    <th style="width: 8%">Date paiement</th>
                                    <th style="width: 8%">Année</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="l in byMasse.datas['recettes']">
                                    <td>{{ l.refPiece }}</td>
                                    <td><button @click="details = l" class="btn btn-default">{{ l.details.length }}</button></td>
                                    <td>{{ l.types ? l.types.join(',') : '' }}</td>
                                    <td>{{ l.text.join(', ') }}</td>
                                    <td style="text-align: right">{{ l.montant.toFixed(2) }}</td>
                                    <td>{{ l.compteBudgetaire.join(', ') }}</td>
                                    <td>{{ l.datecomptable }}</td>
                                    <td>{{ l.datepaiement }}</td>
                                    <td>{{ l.annee }}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr style="font-weight: bold; font-size: 1.2em">
                                    <td colspan="4" style="text-align: right">Total : </td>
                                    <td style="text-align: right">{{ byMasse.totaux['recettes'].toFixed(2) }}</td>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  SpentLinePFI --filename.js SpentLinePFI.js --dist public/js/oscar/dist public/js/oscar/src/SpentLinePFI.vue


    export default {
        props: ['moment', 'url', 'masses'],

        data() {
            return {
                state: "masse",
                error: null,
                pendingMsg: "",
                spentlines: null,
                details: null
            }
        },

        computed: {
            byMasse(){
                let out = {
                    datas: {
                        'N.B': [],
                        'recettes': []
                    },
                    totaux: {
                        'N.B': 0.0,
                        'recettes': 0.0
                    }
                };

                for( let k in this.masses ){
                    out.datas[k] = [];
                    out.totaux[k] = 0.0;
                }

                for( let s in this.spentlines ){
                    let groupedLine = this.spentlines[s];

                    for( let indexAnnexe in groupedLine.masse ){
                        let annexe = groupedLine.masse[indexAnnexe];
                        console.log(annexe);
                        if( !annexe )
                            annexe = 'N.B';

                        if( annexe == '1' ){
                            out.datas['recettes'].push(groupedLine);
                            out.totaux.recettes += groupedLine.montant;
                        } else {
                            out.datas[annexe].push(groupedLine);
                            out.totaux[annexe] += groupedLine.montant;
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


            ////////////////////////////////////////////////////////////////
            //
            // OPERATIONS REST
            //
            ////////////////////////////////////////////////////////////////

            /**
             * Chargement des jalons depuis l'API
             */
            fetch() {
                this.pendingMsg = "Chargement des dépense pour " + this.pfi;

                this.$http.get(this.url).then(
                    success => {
                        this.spentlines = success.data.spents;
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