<template>
    <section class="milestones">
        <h2><i class="icon-calendar"></i>Jalons</h2>

        <transition name="fade">
            <div class="error overlay" v-if="error">
                <div class="overlay-content">
                    <i class="icon-warning-empty"></i>
                    {{ error }}
                    <br>
                    <a href="#" @click="error = null" class="btn btn-sm btn-default btn-xs">
                        <i class="icon-cancel-circled"></i>
                        Fermer</a>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="pending overlay" v-if="pendingMsg">
                <div class="overlay-content">
                    <i class="icon-spinner animate-spin"></i>
                    {{ pendingMsg }}
                </div>
            </div>
        </transition>

        <transition name="fade">

            <div class="overlay" v-if="formData">
                <div class="overlay-content">

                    <h3 v-if="formData.id">Modification du jalon <strong>{{ formData.type.label }}</strong></h3>
                    <h3 v-else>Nouveau jalon</h3>

                    <div class="form-group">
                        <label for="">Type de jalon</label>
                        <select name="" id="" v-model="formData.type.id" class="form-control">
                            <optgroup :label="g.label" v-for="g in groupedTypes">
                                <option :value="t.id" v-for="t in g.types">{{ t.label }}</option>
                            </optgroup>

                        </select>
                        <p v-show="formTypeFinishable" class="help">
                            <i class="icon-info-circled"></i>
                            Ce type de jalon inclut des méchanismes de validation pour marquer le jalon comme terminé
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="">Date prévue pour le jalon</label>
                        <datepicker :moment="moment" :value="formData.dateStart" @input="value => {formData.dateStart = value}"/>
                    </div>

                    <div class="form-group">
                        <label for="">Description</label>
                        <textarea v-model="formData.comment" class="form-control"></textarea>
                    </div>

                    <nav>
                        <button class="btn btn-default" @click="performSave">
                            <i class="icon-trash"></i>
                            Enregistrer
                        </button>
                        <button class="btn btn-default" @click="formData = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="deleteconfirm overlay" v-if="deleteMilestone">
                <div class="overlay-content">
                    <h3><i class="icon-help-circled"></i>
                        Supprimer ce jalon ?</h3>
                    <p>Cette suppression sera <strong>définitive</strong>, si vous souhaitez signifier que ce jalon est réalisé, utilisez plutôt l'option <em>Marquer comme terminé</em>. Si cette option n'est pas disponible, demandez à l'administrateur Oscar si vous avez les privilèges pour réaliser cette action ou si le type de jalon <strong>{{ deleteMilestone.type.label }}</strong> est correctement configuré.</p>
                    <nav>
                        <button class="btn btn-default" @click="preformDelete">
                            <i class="icon-trash"></i>
                            Supprimer
                        </button>
                        <button class="btn btn-default" @click="deleteMilestone = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="validconfirm overlay" v-if="validMilestone">
                <div class="overlay-content">
                    <h3>
                        <i class="icon-help-circled"></i>
                        Valider ce jalon ?
                    </h3>
                    <p>Les jalons marqués comme terminés ne feront pas l'objet de notifications ou d'alertes.</p>
                    <nav>
                        <button class="btn btn-default" @click="performValid('valid')">
                            <i class="icon-ok-circled"></i>
                            Marquer ce jalon comme terminé
                        </button>
                        <button class="btn btn-default" @click="validMilestone = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="inprogressconfirm overlay" v-if="inProgressMilestone">
                <div class="overlay-content">
                    <h3>
                        <i class="icon-help-circled"></i>
                        Marquer ce jalon "en cours" ?
                    </h3>
                    <p> </p>
                    <nav>
                        <button class="btn btn-default" @click="performValid('inprogress')">
                            <i class="icon-cw-outline"></i>
                            Marquer ce jalon comme en cours
                        </button>
                        <button class="btn btn-default" @click="inProgressMilestone = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="inprogressconfirm overlay" v-if="actionMessage">
                <div class="overlay-content">
                    <h3>
                        <i class="icon-help-circled"></i>
                        {{ actionMessage }} ?
                    </h3>
                    <p>Les jalons marqués comme terminés (Validé, refusé ou sans suite) ne feront pas l'objet de notifications ou d'alertes</p>
                    <nav>
                        <button class="btn btn-default" @click="performValid(action)">
                            <i class="icon-cw-outline"></i>
                            {{ actionMessage }}
                        </button>
                        <button class="btn btn-default" @click="handlerActionCancel">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </div>
            </div>
        </transition>



        <transition name="fade">
            <div class="validconfirm overlay" v-if="unvalidMilestone">
                <div class="overlay-content">
                    <h3>
                        <i class="icon-help-circled"></i>
                        Invalider ce jalon ?
                    </h3>
                    <p>L'état d'avancement du jalon sera réinitialisé.</p>
                    <nav>
                        <button class="btn btn-default" @click="performValid('unvalid')">
                            <i class="icon-ok-circled"></i>
                            Réinitialiser la progression de ce jalon
                        </button>
                        <button class="btn btn-default" @click="unvalidMilestone = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <section class="list" v-if="model.milestones != null">
            <p><small>Il y a {{ milestones.length }} jalon(s)</small></p>
            <milestone :milestone="m" v-for="m in milestones" :key="m.id"
                    @valid="handlerValid"
                    @unvalid="handlerUnvalid"
                    @inprogress="handlerInProgress"
                    @cancel="handlerActionConfirm($event, 'cancel','Marquer ce jalon comme sans suite')"
                    @refused="handlerActionConfirm($event, 'refused','Marquer ce jalon comme refusé')"
                    @remove="handlerRemove"
                    @edit="handlerEdit"
            />
        </section>

        <nav class="text-right">
            <a href="#" @click.prevent="handlerNew" v-show="creatable" class="oscar-link">
                <i class="icon-calendar-plus-o"></i>
                Nouveau Jalon
            </a>
        </nav>

    </section>

</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  Milestones --filename.js Milestones.js --dist public/js/oscar/dist public/js/oscar/src/Milestones.vue

    //////////////////////////////////////////////////////////////
    import MilestoneItem from './MilestoneItem.vue'
    import Datepicker from './Datepicker.vue'


    export default {
        props: ['moment', 'url', 'model'],

        components: {
            'milestone': MilestoneItem,
            'datepicker': Datepicker
        },
        data() {
            return {
                error: null,
                formData: null,
                pendingMsg: "",
                creatable: false,
                deleteMilestone: null,
                editMilestone: null,
                validMilestone: null,
                cancelMilestone: null,
                refusedMilestone: null,
                unvalidMilestone: null,
                inProgressMilestone: null,

                //
                action: null,
                actionMessage: "",
                actionMilestone: null
            }
        },

        computed: {
            //// MODEL
            types(){
                return this.model.types;
            },

            milestones(){
                let milestones = [];

                this.model.payments.forEach( payment => {

                    // Récupération de la bonne date
                    let datePayment = new Date(),
                        late = false,
                        done = false,
                        comment;
                    switch( payment.status ){
                        case 1 :
                            datePayment = payment.datePredicted;
                            if( !datePayment ) {
                                comment = "ERREUR DE DATE"
                            } else {
                                comment = "PRÉVU";
                                late = this.moment(payment.datePredicted.date).unix() < this.moment().unix();
                                if( late )  comment += " EN RETARD";
                            }

                            break;
                        case 2 :
                            datePayment = payment.datePayment;
                            comment = "RÉALISÉ";
                            done = true;
                            break;
                        default:
                            return;
                    }
                    if( !datePayment )
                        datePayment = new Date();

                    milestones.push({
                        dateStart: datePayment,
                        comment: 'VERSEMENT ' + comment,
                        deletable: false,
                        late: late,
                        done: done,
                        editable: false,
                        validable: false,
                        isPayment: true,
                        type: {
                          label: 'Versement de ' + payment.amount + payment.currency.symbol,
                          facet: 'payment'
                        }
                    });
                });

                this.model.milestones.forEach( milestone => {
                    milestones.push(milestone);
                });

                milestones.sort( (a, b) => {
                    let vA = this.moment(a.dateStart.date).unix();
                    let vB = this.moment(b.dateStart.date).unix();
                    return vA - vB;
                });

               return milestones;
            },

            payments(){
                return this.model.payments;
            },

            formTypeFinishable(){
                if( !this.formData )
                    return false;
                return this.types.find( type => type.id == this.formData.type.id && type.finishable );
            },

            groupedTypes(){
                let groupedTypes = {};
                this.model.types.forEach( type => {
                    let facet = type.facet;
                    if(!groupedTypes.hasOwnProperty(facet) ){
                        groupedTypes[facet] = {
                            label: facet,
                            types: []
                        };
                    }
                    groupedTypes[type.facet].types.push(type);
                });
                return groupedTypes;
            }
        },

        methods: {
            ////////////////////////////////////////////////////////////////
            //
            // HANDLERS
            //
            ////////////////////////////////////////////////////////////////

            /**
             * Demande de validation
             */
            handlerValid(milestone) {
                this.validMilestone = milestone;
            },

            handlerInProgress(milestone) {
                this.inProgressMilestone = milestone;
            },

            handlerUnvalid(milestone) {
                this.unvalidMilestone = milestone;
            },

            handlerActionConfirm(milestone, action, actionMessage){
                this.actionMilestone = milestone;
                this.action = action;
                this.actionMessage = actionMessage;
            },

            handlerActionCancel(){
                this.actionMilestone = null;
                this.action = null;
                this.actionMessage = "";
            },

            handlerCancel(milestone) {
                this.unvalidMilestone = milestone;
            },

            /**
             * Demande de suppression
             */
            handlerRemove(milestone) {
                this.deleteMilestone = milestone;
            },

            /**
             * Édition : Hydratation du formulaire
             */
            handlerEdit(milestone) {
                this.editMilestone = milestone;
                this.formData = {
                    type: milestone.type,
                    id: milestone.id,
                    comment: milestone.comment,
                    dateStart: this.getMoment()(milestone.dateStart.date).format('YYYY-MM-DD'),
                };
            },

            /**
             * Création : Hydratation du formulaire
             */
            handlerNew() {
                this.formData = {
                    id: 0,
                    type: JSON.parse(JSON.stringify(this.types[0])),
                    dateStart: this.getMoment()().format('YYYY-MM-DD'),
                    comment: ""
                };
            },


            ////////////////////////////////////////////////////////////////
            //
            // OPERATIONS REST
            //
            ////////////////////////////////////////////////////////////////

            /**
             * Suppression : Envoi REST
             */
            preformDelete() {
                this.pendingMsg = "Suppression du jalon";
                this.$http.delete(this.url + "?id=" + this.deleteMilestone.id).then(
                    success => {
                        this.getMilestones();
                    },
                    error => {
                        this.error = "Impossible de supprimer le jalon " + error.body;
                    }
                ).then(foo => {
                    this.pendingMsg = null;
                    this.deleteMilestone = null;
                })
            },

            /**
             * Marquer le jalon comme terminé.
             */
            performValid(action){

                console.log(action);

                var datas = new FormData(),
                    milestone;


                switch (action) {
                    case 'valid':
                        this.pendingMsg = "Validation du jalon";
                        milestone = this.validMilestone;
                        break;

                    case 'unvalid':
                        this.pendingMsg = "Réinitialisation du jalon";
                        milestone = this.unvalidMilestone;
                        break;

                    case 'inprogress':
                        this.pendingMsg = "Marquage du jalon comme en cours";
                        milestone = this.inProgressMilestone;
                        break;

                    case 'cancel':
                    case 'refused':
                        milestone = this.actionMilestone;
                        break;

                    default :
                        this.error = "Action incorrecte";
                        return;
                        break;
                }


                datas.append('id', milestone.id)
                datas.append('action', action)

                this.action = null;
                this.actionMessage = "";
                this.actionMilestone = null;

                this.$http.post(this.url, datas).then(
                    success => {
                        this.getMilestones();
                    },
                    error => {
                        this.error = "Impossible de modifier l'état du jalon : " + error.body;
                    }
                ).then(foo => {
                    this.pendingMsg = null;
                    this.validMilestone = null;
                    this.unvalidMilestone = null;
                    this.inProgressMilestone = null;
                })
            },

            /**
             * Enregistrement des données (Création ou édition)
             */
            performSave() {
                var datas = new FormData();

                datas.append('id', this.formData.id)
                datas.append('type', this.formData.type.id)
                datas.append('comment', this.formData.comment)
                datas.append('dateStart', this.formData.dateStart)
                datas.append('action', this.formData.id ?'update' : 'create')

                this.pendingMsg = this.formData.id ? "Enregistrement des modifications" : "Création du nouveau jalon";

                this.$http.post(this.url, datas).then(
                    success => {
                        this.getMilestones();
                    },
                    error => {
                        this.error = "Impossible d'enregistrer le jalon " + error;
                    }
                ).then(foo => {
                    this.pendingMsg = null;
                    this.formData = null;
                })
            },

            /**
             * Chargement des jalons depuis l'API
             */
            getMilestones() {
                this.pendingMsg = "Chargement des jalons : " + this.url;

                this.$http.get(this.url).then(
                    success => {
                        this.model.milestones = success.data.milestones;
                        this.model.types = success.data.types;
                        this.creatable = success.data.creatable;
                    },
                    error => {
                        this.error = "Impossible de charger les jalons de cette activités : " + error
                    }
                ).then(n => { this.pendingMsg = ""; });
            },

            ////////////////////////////////////////////////////////////////
            //
            // DEPENDENCIES
            //
            ////////////////////////////////////////////////////////////////

            /**
             * @return moment
             */
            getMoment() {
                return this.moment;
            },
        },

        mounted() {
            this.getMilestones()
        }
    }
</script>