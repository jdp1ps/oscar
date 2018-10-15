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
        <h1>Liste des déclarations</h1>
        <section v-for="line,k in declarations" class="card declaration" @click="line.open = !line.open">
            <strong>{{ line.person }}</strong> <time>{{ line.period }}</time>
            <span class="validations-icon">
                <i class="icon" :class="'icon-' +d.status" v-for="d in line.declarations" :title="d.label"></i>
            </span>
            <nav>
                <a href="#" class="btn btn-danger btn-xs" @click="handlerCancelDeclaration(line)"> <i class="icon-trash"></i>Annuler cette déclaration</a>
            </nav>
            <section class="validations text-small" v-show="line.open">
                <article v-for="validation in line.declarations" class="validation">

                    <span>
                        <i :class="validation.object == 'activity' ? 'icon-cube' : 'icon-tag'"></i>
                        <strong>{{ validation.label }}</strong>
                    </span>
                    <em>
                        État : <i :class="'icon-' +validation.status"></i>
                    </em>
                </article>
            </section>
        </section>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  TimesheetDeclarationsList --filename.css TimesheetDeclarationsList.css --filename.js TimesheetDeclarationsList.js --dist public/js/oscar/dist public/js/oscar/src/TimesheetDeclarationsList.vue
    import AjaxResolve from "./AjaxResolve";



    export default {
        name: 'TimesheetDeclarationsList',

        props: {
            moment: {required: true},
            bootbox: {required: true},
            urlapi: {default: null}
        },

        components: {

        },

        data() {
            return {
                loading: null,
                declarations: [],
                error: null
            }
        },

        filters: {

        },

        methods: {


            fetch(clear = true) {
                this.loading = "Chargement de la période";

                this.$http.get('').then(
                    ok => {
                        for( let item in ok.body ){
                            ok.body[item].open = false;
                        }
                        this.declarations = ok.body
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                    }
                ).then(foo => {
                    this.loading = false
                });
            },

            handlerCancelDeclaration(declaration){
                console.log(declaration);
                this.bootbox.confirm("Supprimer la déclaration (le déclarant devra réenvoyer la déclaration) ?", ok => {
                    if( ok ){
                        this.loading = "Suppression de la déclaration";

                        this.$http.delete('?person_id=' + declaration.person_id +"&period=" +declaration.period).then(
                            ok => {
                                this.fetch();
                            },
                            ko => {
                                this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                            }
                        ).then(foo => {
                            this.loading = false
                        });
                    }
                })

            },
        },

        mounted() {
            this.fetch(true)
        }
    }
</script>