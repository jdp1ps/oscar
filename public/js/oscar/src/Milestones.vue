<template>
    <section class="milestones">
        <h1>
            <i class="icon-calendar"></i>
            Jalons UP</h1>
        <transition name="fade">
            <div class="error overlay" v-if="error">
                <div class="overlay-content">
                    <i class="icon-warning-empty"></i>
                    {{ error }}
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
                            <option :value="t.id" v-for="t in types">{{ t.label }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="">Date prévu pour le jalon</label>
                        <input v-model="formData.dateStart" class="form-control" type="date" />
                    </div>

                    <div class="form-group">
                        <label for="">Description</label>
                        <textarea v-model="formData.comment" class="form-control"></textarea>
                    </div>

                    <nav>
                        <button class="btn btn-default" @click="saveFormData">
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
                    <i class="icon-help-circled"></i>
                    Supprimer définitivement ce jalon ?
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

        <div class="alert" v-if="error">
            {{ error }}
        </div>

        <section class="list" v-if="milestones != null">
            <p>Il y'a {{ milestones.length }} jalon(s)</p>
            <milestone :milestone="m" v-for="m in milestones" :key="m.id"
                @valid="handlerValid"
                @remove="handlerRemove"
                @edit="handlerEdit"
            />
        </section>

        <button class="btn btn-default" @click="handlerNew">
            <i class="icon-calendar-plus-o"></i>
            Nouveau Jalon</button>

    </section>

</template>
<script>
    import MilestoneItem from './MilestoneItem.vue'

    export default {
        props: ['moment', 'url'],

        components: {
            'milestone': MilestoneItem
        },

        data(){
            return {
                error: null,
                formData: null,
                pendingMsg: "",
                milestones: null,
                deleteMilestone: null,
                editMilestone: null,
                validMilestone: null,
                types: null
            }
        },

        methods: {
            handlerValid(milestone){
                this.validMilestone = milestone;
            },

            handlerRemove(milestone){
                this.deleteMilestone = milestone;
            },

            preformDelete(){
                this.pendingMsg = "Suppression du jalon";
                this.$http.delete(this.url+"?id=" + this.deleteMilestone.id).then(
                    success => {
                        this.getMilestones();
                    },
                    error => {
                        console.log(error);
                        this.error = "Impossible de supprimer le jalon " + error.body;
                    }
                ).then( foo => {
                    this.pendingMsg = null;
                    this.deleteMilestone = null;
                })
            },

            handlerEdit(milestone){
                this.editMilestone = milestone;
                this.formData = {
                    type: milestone.type,
                    id:milestone.id,
                    comment: milestone.comment,
                    dateStart: this.getMoment()(milestone.dateStart.date).format('YYYY-MM-DD'),
                };
            },

            handlerNew(){
                this.formData = {
                    id: 0,
                    type: JSON.parse(JSON.stringify(this.types[0])),
                    dateStart: this.getMoment()().format('YYYY-MM-DD'),
                    comment: "Commentaire par defaut"
                };
                console.log(this.formData.dateStart);
            },

            getMoment(){
                return this.moment;
            },

            saveFormData(){


                var datas = new FormData();
                datas.append('id', this.formData.id)
                datas.append('type', this.formData.type.id)
                datas.append('comment', this.formData.comment)
                datas.append('dateStart', this.formData.dateStart)

                if( this.formData.id ){
                    this.pendingMsg = "Enregistrement des modifications";
                    this.$http.post(this.url, datas).then(
                        success => {
                            this.getMilestones();
                        },
                        error => {
                            this.error = "Impossible d'enregistrer le jalon " + error;
                        }
                    ).then( foo => {
                        this.pendingMsg = null;
                        this.formData = null;
                    })
                }else{
                    this.pendingMsg = "Création du nouveau jalon";
                    this.$http.put(this.url, datas).then(
                        success => {
                            this.getMilestones();
                        },
                        error => {
                            this.error = "Impossible de créer le jalon " + error;
                        }
                    ).then( foo => { this.pendingMsg = null; this.formData = null; })
                }
            },

            /**
             * Chargement des jalons depuis l'API
             */
            getMilestones(){

                this.pendingMsg = "Chargement des jalons : " + this.url;

                this.$http.get(this.url).then(
                    success => {
                        console.log('Données chargée', success.data);
                        this.milestones = success.data.milestones;
                        this.types = success.data.types;
                    },
                    error => {
                        this.error = "Impossible de charger les jalons de cette activités : " + error
                    }
                ).then( n => this.pendingMsg = "");
            }
        },

        mounted(){
            this.getMilestones()
        }
    }
</script>