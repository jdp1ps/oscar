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
                    <h3>Supprimer la demande <strong>{{ deleteData.label }}</strong> ?</h3>
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
        <h1>Demandes d'activité en attente</h1>
        <section v-if="activityRequests.length">
        <article v-for="a in activityRequests" class="card">
            <h3 class="card-title">
                <strong>
                    <i class="icon-cube"></i>
                    {{ a.label }}</strong>
                <strong>
                    <i class="icon-bank"></i>
                    {{ a.amount }}
                </strong>
                <small class="right">par <strong>{{ a.requester }}</strong></small>
            </h3>
        </article>
        </section>
        <div v-else>
            <p class="alert alert-info">
                Aucune demande
            </p>
        </div>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  ActivityRequestAdmin --filename.css ActivityRequestAdmin.css --filename.js ActivityRequestAdmin.js --dist public/js/oscar/dist public/js/oscar/src/ActivityRequestAdmin.vue

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
                deleteData: null,
                allowNew : false,
                demandeur : "",
                demandeur_id : null,
                organisations : [],
                lockMessages : []
            }
        },

        props: {
            moment: {
                required: true
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

            /**
             * Récupération des données.
             */
            fetch(){
                this.loading = "Chargement des Demandes";
                this.$http.get('?').then(
                    ok => {
                        this.activityRequests = ok.body.activityRequests;
                        this.allowNew = ok.body.allowNew;
                            this.demandeur = ok.body.demandeur;
                            this.demandeur_id = ok.body.demandeur_id;
                            this.organisations = ok.body.organisations;
                            this.lockMessages = ok.body.lockMessages;
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

            handlerSend(demande){
                let form = new FormData();
                form.append('action', 'send');
                form.append('id', demande.id);

                this.$http.post('?', form)
                    .then( ok => {
                        this.fetch();
                    })
                    .catch( err => {
                        this.error = err.body;
                    })
            },

            handlerNew(){
                this.formData = {
                    id: null,
                    label: "",
                    description: "",
                    dateStart: null,
                    dateEnd: null,
                    amount: 0.0,
                    organization: this.organisations[0],
                    files: []
                };
            },

            handlerEdit( demande ){

                this.formData = {
                    id: demande.id,
                    label: demande.label,
                    description: demande.description,
                    dateStart: demande.dateStart,
                    dateEnd: demande.dateEnd,
                    amount: demande.amount,
                    organization: demande.organization,
                    files: demande.files,
                };
                /****/
            },

            handlerDeleteFile(f, a){
                this.loading = "Suppression du fichier " + f.name;
                this.$http.get('?rdl=' + f.file + '&id=' + a.id).then(
                    ok => {
                        this.fetch();
                    }
                ).catch( err => {
                    this.error = err.body;
                }).finally( foo => {
                    this.loading = "";
                })
            },

            handlerSave( evt ){
                let upload = new FormData(evt.target);
                upload.append('dateStart', this.formData.dateStart);
                upload.append('dateEnd', this.formData.dateEnd);
                this.$http.post('?', upload).then(
                    ok => {
                        this.fetch();
                        this.formData = null;
                    }).catch(
                    err => {
                        this.error = err.body;
                        this.formData = null;
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
                this.loading = "Suppression de la demande " + this.deleteData.label;
                let request = this.deleteData;
                this.$http.delete('?id=' + request.id).then(
                    ok => {
                        this.fetch();
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