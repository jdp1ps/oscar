<template>
    <section style="position: relative" class="depenses-previsionnelle">

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
            <header class="line">
                <div class="intitule">
                    Nature des dépenses
                </div>
                <div class="year" v-for="year in years">
                    {{ year }}
                </div>
                <div class="total">Total</div>
            </header>

            <section class="search">
                <input type="search" v-model="filter" class="form-control" placeholder="Rechercher..."/>
                <h1>{{ filter }}</h1>
            </section>
            <section v-if="types">
                <estimatedspentactivityitem :type="t" :years="years" v-for="t in types.children" :key="t.id" v-if="t" :filter="filter" :values="values" @changevalue="handlerUpdateValue($event)"/>
            </section>
            <!--<pre>{{ types.children }}</pre>-->

        </section>
    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  EstimatedSpentActivity --filename.css EstimatedSpentActivity.css --filename.js EstimatedSpentActivity.js --dist public/js/oscar/dist public/js/oscar/src/EstimatedSpentActivity.vue

    export default {
        props: {
            types: { required: true },
            years: { required: true },
            values: { required: true }
        },

        data(){
            return {
                filter: ""
            }
        },

        methods: {
            handlerUpdateValue(datas){
                console.log(datas);
                this.values[datas.id][datas.year] = datas.value;
            }
        }
    }
</script>