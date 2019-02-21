<template>
    <section>
        <div class="alert alert-info" v-show="loading">{{ loading }}</div>

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
                    <h3>Supprimer <strong>{{ deleteData }}</strong> ?</h3>
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

        <transition name="fade">
            <div class="overlay" v-if="formData != null">
                <div class="overlay-content">
                    <h3>Intitulé</h3>
                    <input type="text" v-model="formData" class="input-lg form-control" />
                    <nav>
                        <button type="reset" class="btn btn-danger" @click.prevent="formData = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success" @click.prevent="handlerSubmit">
                            <i class="icon-ok-circled"></i>
                            Confirmer
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <article v-for="t in datas" class="card" :class="t.active ? 'active': 'disabled'">
            <h3 class="card-title">
                <span>
                    {{ t }}
                </span>
                <small class="right">
                    <a href="#" @click.prevent="handlerDelete(t)">
                        <i class="icon-trash"></i>
                        Supprimer</a>
                </small>
            </h3>
        </article>
        <hr>
        <button type="button" class="btn btn-primary" @click.prevent="handlerNew">
            <i class="icon-plus-circled"></i>
            Nouvelle Valeur
        </button>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  ConfigStringList --filename.css ConfigStringList.css --filename.js ConfigStringList.js --dist public/js/oscar/dist public/js/oscar/src/ConfigStringList.vue

    export default {
        data(){
            return {
                formData: null,
                loading: "",
                datas: null,
                error: null,
                deleteData: null
            }
        },

        computed:{
            disciplines(){
                return this.disc;
            }
        },

        methods:{
            fetch(){
                this.loading = "Chargement des donnèes";
                this.$http.get('?').then(
                    ok => {
                        this.datas = ok.body;
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo =>{
                    this.loading = null;
                });
            },

            handlerSubmit(){
                let data = new FormData();

                data.append('str', this.formData);

                this.$http.post('?', data).then(
                    ok => {
                      this.fetch();
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).finally( foo => {
                    this.formData = null;
                });
            },

            handlerNew(){
                this.formData = "";
            },

            handlerCancelForm(){
                this.formData = null;
            },

            handlerDelete( item ){
                this.deleteData = item;
            },

            performDelete(){
                let item = this.deleteData;
                this.$http.delete('?str=' + item).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo => this.deleteData = null );
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>