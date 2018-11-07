<template>
    <section class="validations-admin">
        <transition name="fade">
            <div class="pending overlay" v-if="loading">
                <div class="overlay-content">
                    <i class="icon-spinner animate-spin"></i>
                    {{ loading }}
                </div>
            </div>
        </transition>
        <transition name="fade">
            <div class="pending overlay" v-if="error">
                <div class="overlay-content">
                    <i class="icon-attention-1"></i>
                    {{ error }}
                </div>
            </div>
        </transition>

        TEST
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  PersonSchelude --filename.css PersonSchelude.css --filename.js PersonSchelude.js --dist public/js/oscar/dist public/js/oscar/src/PersonSchelude.vue
    import AjaxResolve from "./AjaxResolve";




    export default {
        name: 'PersonSchelude',

        props: {
            urlapi: {default: null},
            editable: { default: false }
        },

        data() {
            return {
                loading: null,
                error: null
            }
        },

        methods: {

            fetch(clear = true) {
                this.loading = "Chargement des données";

                this.$http.get('').then(
                    ok => {

                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                    }
                ).then(foo => {
                    this.loading = false
                });
            }
        },

        mounted() {
            this.fetch(true)
        }
    }
</script>