<template>
    <section class="spentlines">
        <h2><i class="icon-calculator"></i>Dépenses</h2>

        <pre>{{ spentlines }}</pre>

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

        <section class="list" v-if="spentlines != null">

            <article v-for="line in spentlines">
                <pre>{{ line }}</pre>
            </article>
        </section>
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
                spentlines: null
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