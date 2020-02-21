<template>
    <section>
        <div class="alert alert-info" v-show="loading">{{ loading }}</div>
        <transition name="fade">
            <div class="overlay" v-if="formData">
                <form action="" @submit.prevent="handlerSubmit">
                    <h1 v-if="formData.exist == ''">Nouvel accès</h1>
                    <h1 v-else>Modification de <strong>{{ formData.exist }}</strong></h1>
                    <input type="hidden" name="id" :value="formData.id" />
                    <div>
                        <label for="form_label">Intitulé</label>
                        <input type="text" class="form-control lg" name="login" v-model="formData.login" id="form_label" />
                    </div>

                    <hr>
                    <div>
                        <label for="form_label"><i class="icon-lock-1"></i>  Mot de passe</label>
                        <input type="hidden" name="pass" :value="formData.pass">
                        <div class="row">
                            <div class="col-md-9">
                                <pre class="alert alert-info">
                                    {{ formData.pass }}
                                </pre>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-default" @click="newPassword" type="button">
                                    <i class="icon-cw-outline"></i> Générer un nouveau mot de passe
                                </button>
                            </div>
                        </div>
                    </div>

                    <ul>
                        <li v-for="a,k in apis">
                            <label>
                                <input type="checkbox" name="apis[]" @change="handleToogleApi(k)" :checked="formData.apis.indexOf(k) >= 0" />
                                {{ a }} <small>({{k}})</small>
                                <select v-if="formats[k]" v-model="formData.strategies[k]">
                                    <option value="">Normal</option>
                                    <option :value="label" v-for="classe, label in formats[k]">{{ label }} ({{classe}})</option>
                                </select>
                            </label>
                        </li>
                    </ul>
                    <nav>
                        <button type="submit" class="btn btn-primary" :class="{ 'disabled' : formData.label == '' }" >
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
                    <a href="#" @click.prevent="handlerEdit(a, id)">
                        <i class="icon-pencil-1"></i>
                        Modifier</a>
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
    // node node_modules/.bin/poi build --format umd --moduleName  APIAccess --filename.css APIAccess.css --filename.js APIAccess.js --dist public/js/oscar/dist public/js/oscar/src/APIAccess.vue


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
            apis: { required: true },
            formats: { required: true }
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
            newPassword(){
                this.formData.pass = makeid(32)
            },

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

                if( this.formData.id )
                    data.append('id', this.formData.id);

                data.append('login', this.formData.login);
                data.append('pass', this.formData.pass);
                data.append('apis', this.formData.apis.join(','));
                data.append('strategies', JSON.stringify(this.formData.strategies));

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
                    login: "",
                    exist: "",
                    pass: makeid(32),
                    strategies:{
                        persons: "",
                        organizations: "",
                        activities: "",
                        roles: "",
                    },
                    apis:[],
                    active: false
                };
            },

            handlerEdit(a, key){
                this.formData = a;
                this.formData.exist = key;
                this.formData.login = key;
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