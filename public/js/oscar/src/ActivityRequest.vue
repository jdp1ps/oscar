<template>
    <section>
        <div class="alert alert-info" v-show="loading">{{ loading }}</div>

        <transition name="fade">
            <div class="overlay" v-if="formData">
                <form action="?" @submit.prevent="handlerSave($event)" enctype="multipart/form-data" method="post" name="save" style="min-width: 75vw">
                    <!--
                    <h1 v-if="formData.original">Modifier {{ formData.original }}</h1>
                    <h1 v-else="formData.original">Nouvelle discipline</h1>
                    -->
                    <div class="columns">
                        <div class="col6">
                            <strong>Demandeur : </strong><br>
                            <span class="cartouche">
                                {{ demandeur }}
                                <span class="addon">Demandeur</span>
                            </span>
                        </div>
                        <div class="col6">
                            <strong>Oragnisme référent : </strong><br>
                            <p v-if="organisations.length == 0" class="alert alert-info">
                                Vous n'êtes associé à aucun organisme.
                            </p>
                            <select name="organisation_id" v-else class="form-control">
                                <option :value="id" v-for="org, id in organisations">{{ org }}</option>
                            </select>
                        </div>
                    </div>
                    <hr class="separator">

                    <div>
                        <label for="form_label">Intitulé</label>
                        <input type="hidden" v-model="formData.id" v-if="formData.id" name="id" />
                        <input type="text" class="form-control input-lg" v-model="formData.label" id="form_label" name="label"/>
                    </div>
                    <hr class="separator">

                    <div class="row">
                        <div class="col-md-4">
                            <strong>Début prévu</strong>
                            <p class="help">Vous pouvez laisser ce champ vide.</p>
                            <datepicker :moment="moment" :value="formData.dateStart" @change="formData.dateStart = $event"/>
                        </div>
                        <div class="col-md-4">
                            <strong>Fin prévue</strong>
                            <p class="help">Vous pouvez laisser ce champ vide.</p>
                            <datepicker  :moment="moment"  :value="formData.dateEnd" @change="formData.dateEnd = $event"/>
                        </div>

                        <div class="col-md-4">
                            <label for="form_amount">Montant souhaité</label>
                            <p class="help">Vous pouvez laisser ce champ vide.</p>
                            <input type="text" class="form-control" name="amount" id="form_amount" v-model="formData.amount">
                        </div>
                    </div>
                    <hr class="separator">

                    <div>
                        <label for="form_description">Description</label>
                        <textarea type="text" class="form-control" v-model="formData.description" id="form_description" name="description"></textarea>
                    </div>
                    <hr class="separator">

                    <div>
                        <label for="form_files">Fichiers à ajouter</label>
                        <p class="help">Vous pouvez sélectionner plusieurs fichiers en maintenant la touche CTRL enfoncé lors de la sélection d'un fichier</p>
                        <input type="file" name="files[]" id="form_files">
                    </div>

                    <div class="alert alert-info">
                        Vous pourrez modifier votre saisie, et finaliser la demande en cliquant sur l'action <strong>Envoyer la demande</strong>
                    </div>

                    <hr>

                    <nav class="text-center">
                        <button type="reset" class="btn btn-default" @click.prevent="handlerCancelForm">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>

                        <button type="submit" class="btn btn-primary">
                            <i class="icon-floppy"></i>
                            Enregistrer
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

        <header class="row">
            <h1 class="col-md-9">{{ title }}</h1>
            <nav class="col-md-3">
                &nbsp;
                <jckselector :choose="listStatus" :selected="selectedStatus" @change="selectedStatus = $event"/>
            </nav>
        </header>

        <section v-if="activityRequests.length">
        <article v-for="a in activityRequests" class="demande card" v-bind:class="'status-' +a.statut">
            <h3 class="card-title">
                <strong>
                    <i :class="'icon-' + a.statutText"></i>
                    {{ a.label }}
                </strong>
                <strong>
                    <i class="icon-bank"></i>
                    {{ a.amount }}
                </strong>
            </h3>
            <div class="card-metas text-highlight">
                <strong><i :class="'icon-' + a.statut"></i>{{ a.statut | renderStatus }}</strong>
                Créé le : <time><i class="icon-calendar"></i>{{ a.dateCreated | date }}</time> ~
                <span v-if="a.worker">
                    Demande géré par  <strong>{{ a.worker }}</strong>
                </span>
                <em v-else>
                    Non prise en charge pour le moment
                </em>
                ~
                <strong>
                    <i class="icon-building-filled"></i>
                    <span v-if="a.organisation">{{ a.organisation }}</span>
                    <em v-else>Aucune organisation</em>
                </strong>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h3><i class=" icon-edit"></i> Informations</h3>
                    <ul>
                        <li><i class="icon-bank"></i> Somme demandée : <strong>{{ a.amount | montant}}</strong></li>
                        <li><i class="icon-calendar"></i> Début (prévu) :
                            <strong v-if="a.dateStart">{{ a.dateStart | date}}</strong>
                            <em v-else>pas de date de début prévue</em>
                        </li>
                        <li><i class="icon-calendar"></i> Fin (prévue) :
                            <strong v-if="a.dateEnd">{{ a.dateEnd | date}}</strong>
                            <em v-else>pas de date de fin prévue</em>
                        </li>
                    </ul>
                    <div class="alert alert-help">
                        <strong>Description</strong> :
                    {{ a.description }}
                    </div>
                    <section class="fichiers">
                        <h3><i class="icon-attach-outline"></i> Fichiers</h3>
                        <div v-if="a.files.length == 0" class="alert alert-info">
                            Vous n'avez fourni aucun document pour cette demande
                        </div>
                        <ul v-else>
                            <li v-for="f in a.files">
                                <strong>{{ f.name }}</strong>
                                <a :href="'?dl=' + f.file + '&id=' + a.id" class="btn btn-default btn-xs">
                                    <i class="icon-download"></i>
                                    Télécharger</a>
                                <a href="#" @click.prevent.stop="handlerDeleteFile(f, a)" class="btn btn-default btn-xs" v-if="a.sendable">
                                    <i class="icon-trash"></i>
                                    Supprimer ce fichier</a>
                            </li>
                        </ul>
                    </section>

                </div>
                <div class="col-md-6">
                    <section>
                        <h3><i class="icon-signal"></i> Suivi</h3>
                        <article v-for="s in a.suivi" class="follow">
                            <figure class="avatar">
                                <img :src="'//www.gravatar.com/avatar/' + s.by.gravatar + '?s=64'"
                                     alt="" />
                            </figure>
                            <div class="content">
                                <small class="infos">
                                    <i class="icon-clock"></i> {{ s.datecreated | date }}
                                    par <strong><i class="icon-user"></i> {{ s.by.username }}</strong>
                                </small>
                                <br>
                                <p>{{ s.description }}</p>
                            </div>
                        </article>
                    </section>
                </div>
            </div>
            <nav v-if="a.sendable">
                <a href="#" @click.prevent.stop="handlerEdit(a)" class="btn btn-primary">
                    <i class="icon-edit"></i>
                    Modifier</a>
                <a href="#" @click.prevent.stop="handlerDelete(a)" class="btn btn-danger">
                    <i class="icon-trash"></i>
                    Supprimer</a>
                <a href="#" @click.prevent.stop="handlerSend(a)" class="btn btn-default">
                    <i class="icon-paper-plane"></i>
                    soumettre</a>
            </nav>
        </article>
        </section>
        <div v-else>
            <p class="alert alert-info">
                Aucune demande
            </p>
        </div>
        <hr>
        <button type="button" class="btn btn-primary" @click.prevent="handlerNew" v-if="allowNew">
            <i class="icon-plus-circled"></i>
            Nouvelle Demande
        </button>
        <div class="alert alert-danger" v-if="lockMessages.length">
            <ul>
                <li v-for="m in lockMessages">{{ m }}</li>
            </ul>
        </div>
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
                deleteData: null,
                allowNew : false,
                demandeur : "",
                demandeur_id : null,
                organisations : [],
                lockMessages : [],
                selectedStatus: [1, 2]
            }
        },

        props: {
            moment: {
                required: true
            },
            title: {
                required: true
            }
        },

        watch: {
            selectedStatus(){
                this.fetch();
            }
        },

        computed:{
            listStatus(){
                let status = [
                    {id: 1, label: "Brouillon", description: "Demandes en cours de rédaction (non envoyées)" },
                    {id: 2, label: "Envoyée", description: "Demandes envoyées mais pas encore traitées" },
                    {id: 5, label: "Validée", description: "Demandes validées" },
                    {id: 7, label: "Refusée", description: "Demandes refusées" }
                ];
                return status;
            }
        },

        components: {
            'jckselector': require('./JCKSelector.vue').default,
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
                this.$http.get('?&status=' + this.selectedStatus.join(',')).then(
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