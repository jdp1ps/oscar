<template>
    <section>
        <transition name="fade">
            <div class="overlay" v-if="error">
                <div class="alert alert-danger overlay-content">
                    <h3>Erreur
                        <a href="#" @click.prevent="error =null" class="float-right">
                            <i class="icon-cancel-outline"></i>
                        </a>
                    </h3>
                    <pre>{{ error }}</pre>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="overlay" v-if="confirmProccess">
                <div class="alert alert-danger overlay-content">
                    <h3>{{ confirmProccess.message }}</h3>

                    <transition name="slide">
                        <div v-if="confirmProccess.step == 2">
                            <button type="reset" class="btn btn-danger" @click.prevent="confirmProccess = null">
                                <i class="icon-cancel-outline"></i>
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-success" @click.prevent="confirmProccess.process()">
                                <i class="icon-ok-circled"></i>
                                Confirmer
                            </button>
                        </div>
                        <div v-else>
                            <section v-if="confirmProccess.person" class="row">
                                <label for="roleDelarer" class="col-md-8">
                                    Rôle de {{ confirmProccess.person }}<br>
                                    <small>Selectionnez un rôle pour <strong>{{ confirmProccess.person }}</strong> dans l'activité de recherche. Vous pourrez modfifier cette information en éditant directement l'activité par le suite
                                    </small>
                                </label>
                                <div class="col-md-4">
                                    <select v-model="confirmProccess.personRole" class="form-control" id="roleDeclarer">
                                        <option value="0">Ne pas affecter à l'activité</option>
                                        <option v-for="r, id in rolesPerson" :value="id">{{ r }}</option>
                                    </select>
                                </div>
                            </section>
                            <section v-if="confirmProccess.organization" class="row">
                                <label for="roleOrg" class="col-md-8">Rôle de {{ confirmProccess.organization }}</label>
                                <div class="col-md-4">
                                    <select v-model="confirmProccess.organisationRole" class="form-control" id="roleOrg">
                                        <option v-for="r in rolesOrganisation" :value="r.id">{{ r.label }}</option>
                                    </select>
                                </div>
                            </section>
                            <hr class="separator">
                            <button type="button" class="btn btn-default" @click="confirmProccess.step = 2">
                                Suivant
                                <i class="icon-right-outline"></i>
                            </button>
                        </div>
                    </transition>
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


        <div class="alert alert-info" v-show="loading">{{ loading }}</div>
        <section v-if="activityRequests.length">
        <article v-for="a in activityRequests" class="card">
            <h3 class="card-title">
                <strong>
                    <i :class="'icon-' + a.statutText"></i>
                    {{ a.label }}</strong>
                <strong>
                    <i class="icon-bank"></i>
                    {{ a.amount }}
                </strong>
                <small class="right">par <strong>{{ a.requester }}</strong></small>
            </h3>
            <div class="content row">
                <div class="col-md-6">
                    <i class="icon-user"></i> Statut : <strong>{{ a.statut | renderStatus }}</strong><br>
                    <i class="icon-user"></i> Demandeur : <strong>{{ a.requester }}</strong><br>
                    <i class="icon-building-filled"></i>Organisme : <strong v-if="a.organisation"> {{ a.organisation }}</strong>
                    <em v-else>Aucun organisme identifié</em><br>
                    <i class="icon-bank"></i> Budget : <strong>{{ a.amount | montant}}</strong><br>
                    <i class="icon-calendar"></i> du <strong v-if="a.dateStart">{{ a.dateStart | date}}</strong><em v-else>non précisé</em> au
                    <strong v-if="a.dateEnd">{{ a.dateEnd | date}}</strong><em v-else>non précisé</em><br>
                    <strong><i class="icon-comment"></i>Description : </strong>
                    {{ a.description }}
                </div>
                <div class="col-md-6">
                    <h3>Suivi</h3>
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
                </div>

            </div>
            <section class="liste-fichiers" v-if="a.files.length">
                <h4><i class="icon-file-excel"></i> Fichiers</h4>
                <ul>
                    <li v-for="f in a.files">
                        <strong>{{ f.name }}</strong>
                        <a :href="'?dl=' + f.file + '&id=' + a.id" class="btn btn-default btn-xs">
                            <i class="icon-download"></i>
                            Télécharger</a>
                    </li>
                </ul>
            </section>
            <nav v-if="a.statut == 2">
                <button class="btn btn-success" @click="handlerValid(a)">
                    <i class="icon-valid"></i> Valider la demande
                </button>
                <button class="btn btn-danger" @click="handlerReject(a)">
                    <i class="icon-cancel-alt"></i> Rejeter la demande
                </button>
            </nav>
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
                lockMessages : [],
                confirmProccess: null,
                history: false,
                selectedStatus: [2],
                roles: {
                    person: null,
                    organisation: null
                }
            }
        },

        components: {
            'jckselector': require('./JCKSelector.vue').default,
        },

        props: {
            moment: {
                required: true
            },
            rolesPerson: {
                required: true
            },
            rolesOrganisation: {
                required: true
            },
            asAdmin: {
                default: false
            },
            title: {
                required: true
            }
        },

        computed:{
            listStatus(){
                let status = [
                    {id: 2, label: "Envoyée", description: "Demandes envoyées" },
                    {id: 5, label: "Validée", description: "Demandes validées" },
                    {id: 7, label: "Refusée", description: "Demandes refusées" }
                ];
                if( this.asAdmin ){
                    status.push(
                    {id: 1, label: "Brouillon", description: "Demandes en cours de rédaction" }
                    )
                }
                return status;
            }
        },

        watch: {
            'history' : function(){
                this.fetch();
            },
            'selectedStatus' : function(){
                this.fetch();
            }
        },

        methods:{
            handlerValid(request){
                this.confirmProccess = {
                    step: 1,
                    message: "Confirmer la transformation de la demande en activité : "+ request.label + " par " + request.requester +" ?",
                    person: request.requester,
                    personRole: 0,
                    organization: request.organisation,
                    organizationRole: 0,
                    process: () => this.performValid(request)
                }
            },

            handlerReject(request){
                this.confirmProccess = {
                    step: 2,
                    message: "Confirmer le rejet de la demande : " + request.label + " par " + request.requester +" ?",
                    process: () => this.performReject(request)
                }
            },

            performValid(request){
              this.loading = "Transformation de la demande en activité...";
              let datas = new FormData();
              datas.append('id', request.id);
              datas.append('action', 'valid');

              if( this.confirmProccess.person ) {
                  datas.append('personRoleId', this.confirmProccess.personRole);
              }
              if( this.confirmProccess.organization ) {
                  datas.append('organisationRoleId', this.confirmProccess.organisationRole);
              }

              this.$http.post('?', datas).then(
                  ok => {
                      this.fetch();
                  },
                  ko => {
                      this.error = "Impossible de valider la demande : " +ko.body;
                  }
              ).then( foo => {
                  this.loading = "";
                  this.confirmProccess = null;
              })
            },

            performReject(request){
                this.loading = "Rejet de la demande en activité...";
                let datas = new FormData();
                datas.append('id', request.id);
                datas.append('action', 'reject');
                this.$http.post('?', datas).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = "Impossible de rejeter la demande : " +ko.body;
                    }
                ).then( foo => {
                    this.loading = "";
                    this.confirmProccess = null;
                })
            },

            /**
             * Récupération des données.
             */
            fetch(){
                this.loading = "Chargement des Demandes";
                this.$http.get('?' + (this.history ? '&history=1': '') +'&status=' +this.selectedStatus.join(',')).then(
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