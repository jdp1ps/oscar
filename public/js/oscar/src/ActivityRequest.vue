<template>
    <section>
        <div class="alert alert-info" v-show="loading">{{ loading }}</div>

        <transition name="fade">
            <div class="overlay" v-if="formData">
                <form action="?" @submit.prevent="handlerSave($event)" enctype="multipart/form-data" method="post" name="save">
                    <!--
                    <h1 v-if="formData.original">Modifier {{ formData.original }}</h1>
                    <h1 v-else="formData.original">Nouvelle discipline</h1>
                    -->
                    <div>
                        <label for="form_label">Intitulé</label>
                        <input type="text" class="form-control lg" v-model="formData.label" id="form_label" name="label"/>
                    </div>

                    <div>
                        <label for="form_description">Description</label>
                        <textarea type="text" class="form-control" v-model="formData.description" id="form_description" name="description"></textarea>
                    </div>

                    <div>
                        <label for="form_files">Fichiers</label>
                        <input type="file" name="files[]" id="form_files">
                    </div>

                    <hr>

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
                    <h3>Supprimer la TVA <strong>{{ deleteData.label }}</strong> ?</h3>
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
        <h1>Demandes d'activité</h1>
        <section v-if="activityRequests.length">
        <article v-for="a in activityRequests" class="card">
            <h3 class="card-title">
                <strong>{{ a.label }}</strong> <small>par {{ a.requester }}</small>
            </h3>
            <div class="card-metas text-highlight">
                <strong><i :class="'icon-' + a.statut"></i>{{ a.statutText }}</strong>
                Créé le : <time><i class="icon-calendar"></i>{{ a.dateCreated | date }}</time> ~
                <span v-if="a.worker">
                    Demande géré par  <strong>{{ a.worker }}</strong>
                </span>
                <em v-else>
                    Non prise en charge pour le moment
                </em>
            </div>
            <hr>
            <div>
                <h4><i class=" icon-edit"></i> Informations</h4>
                {{ a.description }}
            </div>
            <section class="fichiers">
                <h4><i class="icon-attach-outline"></i> Fichiers</h4>
                <div v-if="a.files.length == 0" class="alert alert-info">
                    Vous n'avez fourni aucun document pour cette demande
                </div>
                <article v-else v-for="f in a.files">
                    {{ f }}
                </article>
                <!--
                <form action="?" @submit.prevent="sendFile(a.id, $event)" enctype="multipart/form-data" v-if="addFile" method="post" name="sendfile">
                    <input type="hidden" name="activityrequest_id" :value="a.id" />
                    <input type="hidden" name="action" :value="a.id" />
                    <input type="file" @change="processFile($event)" name="file" />
                    <button type="submit" :class="{'disabled': !addableFiles }" class="btn btn-primary">Envoyer</button>
                </form>
                <button @click="handlerAddFile()" v-else>Ajouter un fichier</button>
                -->
            </section>
            <pre>{{ a }}</pre>
        </article>
        </section>
        <div v-else>
            <p class="alert alert-info">
                Aucune demande
            </p>
        </div>
        <hr>
        <button type="button" class="btn btn-primary" @click.prevent="handlerNew">
            <i class="icon-plus-circled"></i>
            Nouvelle Demande
        </button>
        <pre>{{ addableFiles }}</pre>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  ActivityRequest --filename.css ActivityRequest.css --filename.js ActivityRequest.js --dist public/js/oscar/dist public/js/oscar/src/ActivityRequest.vue

    export default {
        data(){
            return {
                formData: null,
                addFile: false,
                addableFiles: null,
                file: null,
                loading: "",
                activityRequests: [],
                error: null,
                deleteData: null
            }
        },

        computed:{

        },

        methods:{
            processFile(evt){
                this.addableFiles = evt.target.files[0].name;
            },

            handlerAddFile(){
                this.addFile = true;
            },

            fetch(){
                this.loading = "Chargement des Demandes";
                this.$http.get('?').then(
                    ok => {
                        this.activityRequests = ok.body.activityRequests;
                    },
                    ko => {
                        this.error = "Impossible de charger les demandes : " + ko.body;
                    }
                ).then( foo =>{
                    this.loading = null;
                });
            },

            sendFile(id, evt){
                let upload = new FormData(evt.target);
                this.$http.post('?', upload).then(
                    ok => {

                }).catch(
                    err => {

                })
            },

            handlerSubmit(){
                let data = new FormData();
                /*
                data.append('label', this.formData.label);
                data.append('rate', this.formData.rate.toString().replace(/,/g,'.'));
                data.append('active', this.formData.active);
                data.append('id', this.formData.id);

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
                /****/
            },

            handlerNew(){
                this.formData = {
                    id: null,
                    label: "",
                    description: "",
                    files: []
                };
            },

            handlerEdit( demande ){

                this.formData = {
                    id: demande.id,
                    label: demande.label,
                    description: demande.description,
                    files: demande.files
                };
                /****/
            },

            handlerSave( evt ){
                let upload = new FormData(evt.target);
                this.$http.post('?', upload).then(
                    ok => {
                        console.log(ok);
                    }).catch(
                    err => {
                        console.log(err);
                    })
            },

            handlerCancelForm(){
                this.formData = null;
            },


            handlerDelete( request ){
                this.deleteData = request;
            },

            performDelete(){
                /*
                let tva = this.deleteData;
                this.$http.delete('?id=' + tva.id).then(
                    ok => {
                        //this.tvas = ok.body.tvas;
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo => this.deleteData = null );
                /****/
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>