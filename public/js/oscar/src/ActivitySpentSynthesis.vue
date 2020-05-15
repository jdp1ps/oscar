<template>
    <section>

        <transition name="fade">
            <div class="error" v-if="error">
                <i class="icon-warning-empty"></i>
                Les données affichées peuvent ne pas être à jour : 
                {{ error }}
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
                <td style="text-align: right">{{ synthesis[k] | money  }} €</td>
            </tr>
            <tr style="border-top: solid #000 thin" v-if="synthesis['N.B']">
                <th>Hors masse</th>
                <td style="text-align: right">{{ synthesis['N.B'] | money  }} €</td>
            </tr>
            <tr style="border-top: solid #000 thin; font-size: 1.6em">
                <th>TOTAL : </th>
                <td style="text-align: right">{{ synthesis['total'] | money }} €</td>
            </tr>
        </table>

        <small>Données mise à jour : <strong v-if="dateUpdated">{{ dateUpdated.date | dateFull }}</strong></small>

    </section>

</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  ActivitySpentSynthesis --filename.js ActivitySpentSynthesis.js --dist public/js/oscar/dist public/js/oscar/src/ActivitySpentSynthesis.vue


    export default {
        props: ['url'],

        data() {
            return {
                error: null,
                pendingMsg: "",
                synthesis: [],
                masses: {},
                dateUpdated: null
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
                this.pendingMsg = "Chargement des donnèes";

                this.$http.get(this.url).then(
                    success => {
                        this.synthesis = success.data.synthesis;
                        this.masses = success.data.masses;
                        this.dateUpdated = success.data.dateUpdated;
                        this.error = success.data.error;
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