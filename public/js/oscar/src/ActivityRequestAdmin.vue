<template>
    <section>
        <div class="alert alert-info" v-show="loading">{{ loading }}</div>

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
                    <section v-if="confirmProccess.person" class="row">
                        <label for="roleDelarer" class="col-md-4">Rôle de {{ confirmProccess.person }}</label>
                        <div class="col-md-8">
                            <select v-model="confirmProccess.personRole" class="form-control" id="roleDeclarer">
                                <option value="0">Ne pas affecter à l'activité</option>
                                <option v-for="r in rolesPerson" :value="r.id">{{ r.roleId }}</option>
                            </select>
                        </div>
                    </section>
                    <section v-if="confirmProccess.organisation">
                        <label for="roleOrg">Rôle de {{ confirmProccess.organisation }}</label>
                        <select v-model="confirmProccess.organisationRole" class="form-control" id="roleOrg">
                            <option v-for="r in rolesOrganisation" :value="r.id">{{ r.roleId }}</option>
                        </select>
                    </section>
                    <hr class="separator">
                    <nav>
                        <button type="reset" class="btn btn-danger" @click.prevent="confirmProccess = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success" @click.prevent="confirmProccess.process()">
                            <i class="icon-ok-circled"></i>
                            Confirmer
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <h1>Traitement des demandes d'activité en attente</h1>
        <nav>
            <label for="history">
                Afficher l'historique
                <input type="checkbox" v-model="history" id="history" />
            </label>
        </nav>
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
                    <li v-for="f in a.files"><strong>{{ f.name }}</strong>{{ f }}</li>
                </ul>
            </section>
                <!-- <pre>{{ a }}</pre> -->
            <nav v-if="a.statut == 2">
                <button class="btn btn-success" @click="handlerValid(a)">
                    <i class="icon-valid"></i> Valider la demande
                </button>
                <button class="btn btn-default" @click="handlerTaken(a)">
                    <i class="icon-edit"></i> Marquée comme prise en charge
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
                roles: {
                    person: null,
                    organisation: null
                }
            }
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
        },

        computed:{

        },

        watch: {
            'history' : function(){
                this.fetch();
            }
        },

        methods:{
            handlerValid(request){
                this.confirmProccess = {
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
              if( this.confirmProccess.organisation ) {
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

            /*
            (a)">
                    <i class="icon-valid"></i> Valider la demande
                </button>
                <button class="btn btn-default" @click="handlerTaken(a)">
                    <i class="icon-edit"></i> Marquée comme prise en charge
                </button>
                <button class="btn btn-danger" @click="handlerRefuse(a)">
                    <i class="icon-cancel-alt"></i> Rejeter la demande
            */

            /**
             * Récupération des données.
             */
            fetch(){
                this.loading = "Chargement des Demandes";
                this.$http.get('?' + (this.history ? '&history=1': '')).then(
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