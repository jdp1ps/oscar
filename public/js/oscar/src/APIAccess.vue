<template>
    <section>
        <div class="alert alert-info" v-show="loading">{{ loading }}</div>
        <transition name="fade">
            <div class="overlay" v-if="formData">
                <form action="" @submit.prevent="handlerSubmit">
                    <h1>Nouvel accès</h1>

                    <div>
                        <label for="form_label">Intitulé</label>
                        <input type="text" class="form-control lg" name="login" v-model="formData.login" id="form_label" />
                    </div>
                    <hr>
                    <div>
                        <label for="form_label"><i class="icon-lock-1"></i>  Mot de passe</label>
                        <input type="hidden" name="pass" :value="formData.pass">
                        <pre class="alert alert-info">{{ formData.pass }}</pre>
                    </div>

                    <ul>
                        <li v-for="a,k in apis">
                            <label>
                                <input type="checkbox" name="apis[]" @change="handleToogleApi(k)" :checked="formData.apis.indexOf(a) >= 0" />
                                {{ a }}
                            </label>
                        </li>
                    </ul>
                    <nav>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-floppy"></i>
                            Enregistrer
                        </button>
                        <button type="reset" class="btn btn-default" @click.prevent="handlerCancelForm">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </form>
            </div>
        </transition>

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
                    <h3>Supprimer l'accès API <strong>{{ deleteData }}</strong> ?</h3>
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
        <article v-for="a, id in datas" class="card" :class="a.active ? 'active': 'disabled'">
            <h3 class="card-title">
                <strong>{{ id }}</strong>
                <span>
                    <span v-for="a in a.apis" class="label label-primary">{{ a }}</span>
                </span>
                <small class="right">
                    <a href="#" @click.prevent="handlerDelete(id)">
                        <i class="icon-trash"></i>
                        Supprimer</a>
                </small>
            </h3>
            <pre class="card-content">Code : <strong>{{ a.pass }}</strong></pre>
        </article>
        <hr>
        <button type="button" class="btn btn-primary" @click.prevent="handlerNew">
            <i class="icon-plus-circled"></i>
            Nouvel accès
        </button>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  APIAccess --filename.css APIAccess.css --filename.js APIAccess.js --dist public/js/oscar/dist public/js/oscar/src/APIAccess.vue

    function makeid(length) {
        var result           = '';
        var characters       = 'ÉÈÀÊËÇĈ©ÙÛÜÂÄÀéèàêëçĉ©ùûüâäàµ§ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }


    export default {
        props: {
            apis: { required: true }
        },

        data(){
            return {
                formData: null,
                loading: "",
                datas: null,
                error: null,
                deleteData: null
            }
        },

        methods:{
            handleToogleApi(key){
                let index = this.formData.apis.indexOf(key);

                if( index >= 0 ){
                    this.formData.apis.splice(index, 1);
                } else {
                    this.formData.apis.push(key);
                }
            },

            fetch(){
                this.loading = "Chargement des accès API";
                this.$http.get('?').then(
                    ok => {
                        this.datas = ok.body.datas;
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

                data.append('login', this.formData.login);
                data.append('pass', this.formData.pass);
                data.append('apis', this.formData.apis.join(','));

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
                this.formData = {
                    login: "Identifiant",
                    pass: makeid(32),
                    apis:[],
                    active: false
                };
            },

            handlerCancelForm(){
                this.formData = null;
            },

            handlerDelete( d ){
                this.deleteData = d;
            },

            performDelete(){
                this.$http.delete('?id=' + this.deleteData).then(
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