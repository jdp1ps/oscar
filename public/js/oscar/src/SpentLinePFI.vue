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

        <table class="list table table-condensed table-bordered table-condensed card" v-if="spentlines != null">
            <thead>
            <tr>
                <th>N°</th>
                <th>Ligne(s)</th>
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
                <td>{{ l.text.join(', ') }}</td>
                <td style="text-align: right">{{ l.montant.toFixed(2) }}</td>
                <td>{{ l.compteBudgetaire.join(', ') }}</td>
                <td>{{ l.datecomptable }}</td>
                <td>{{ l.datepaiement }}</td>
                <td>{{ l.annee }}</td>
            </tr>
            </tbody>
        </table>
    </section>

</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  SpentLinePFI --filename.js SpentLinePFI.js --dist public/js/oscar/dist public/js/oscar/src/SpentLinePFI.vue


    export default {
        props: ['moment', 'url'],

        data() {
            return {
                error: null,
                pendingMsg: "",
                spentlines: null,
                details: null
            }
        },

        computed: {

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
                            this.error = "Impossible de charger les dépenses pour ce PFI : " + error
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