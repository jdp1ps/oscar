<template>
    <section style="position: relative">

        <transition name="fade">
            <div class="overlay" v-if="error">
                <div class="alert alert-danger">
                    <h3>Erreur
                        <a href="#" @click.prevent="error =null" class="float-right">
                            <i class="icon-cancel-outline"></i>
                        </a>
                    </h3>
                    <p>{{ error }}</p>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="overlay" v-if="deleteData">
                <div class="alert alert-danger">
                    <h3>Supprimer le type <strong>{{ deleteData.label }}</strong> ?</h3>
                    <nav>
                        <button type="reset" class="btn btn-danger" @click.prevent="deleteData = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success" @click.prevent="performDelete">
                            <i class="icon-ok-circled"></i>
                            Confirmer
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <section>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Nature des dépenses</th>
                    <th class="year" v-for="year in years">{{ year }}</th>
                    <th class="total">TOTAL</th>
                </tr>
                </thead>

                <estimatedspentactivityitem :type="t" :years="years" v-for="t in types.children" />


            </table>
            <pre>{{ types }}</pre>
        </section>


        <h3 @click="handlerClick">FORM ICI</h3>
        <pre>{{ years }}</pre>
        <pre>{{ types }}</pre>
    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  EstimatedSpentActivity --filename.css EstimatedSpentActivity.css --filename.js EstimatedSpentActivity.js --dist public/js/oscar/dist public/js/oscar/src/EstimatedSpentActivity.vue

    export default {
        props: {
            types: { required: true },
            years: { required: true }
        },

        data(){
            return {

            }
        },

        methods:{
            handlerClick(){
                console.log('click');
                this.$emit('foo', 'toto');
            }
        },
        mounted(){
            console.log('MOUNTED');
            this.$emit('foo', 'test 1');
        }
    }
</script>