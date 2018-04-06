<template>
    <section class="organizationtype">
        LISTE

        <transition name="fade">
            <div class="error overlay" v-if="error">
                <div class="overlay-content">
                    <i class="icon-warning-empty"></i>
                    {{ error }}
                    <br>
                    <a href="#" @click="error = null" class="btn btn-default">
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
                <form action="" class="form" @submit.prevent="save">
                    <div class="form-group">
                        <label for="form_label">Intitulé</label>
                        <input class="form-control" v-model="formData.label" id="form_label" placeholder="Intitulé" />
                    </div>
                    <div class="form-group">
                        <label for="form_label">Sous Type de : </label>
                        <select name="root_id" id="" v-model="formData.root_id" class="form-control">
                            <option value="">Aucun</option>
                            <option :value="t.id" v-for="t in organizationtypes" v-if="t.id != formData.id">{{ t.label }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="form_description">Description</label>
                        <textarea class="form-control" v-model="formData.description" id="form_description" placeholder=""></textarea>
                    </div>
                    <nav class="text-center">
                        <button type="reset" @click="formData = null" class="btn btn-primary">
                            Annuler</button>
                        <button type="submit" class="btn btn-default">
                            Enregistrer</button>
                    </nav>
                </form>
            </div>
        </transition>

        <article class="card card-xs" v-for="type in organizationtypes" draggable="true">
            <h3 class="card-title">
                {{ type.label }}
                <small>
                    <a href="#" @click.prevent="edit(type)"><i class="icon-floppy"></i> Modifier</a>
                    <a href="#" @click.prevent="remove(type)"><i class="icon-trash"></i> Supprimer</a>
                </small>
            </h3>

                <article v-for="subtype in type.children" class="card card-xs">
                    {{ subtype.label }}
                    <small>
                        <a href="#" @click.prevent="edit(subtype)"><i class="icon-floppy"></i> Modifier</a>
                        <a href="#" @click.prevent="remove(subtype)"><i class="icon-trash"></i> Supprimer</a>
                    </small>
                </article>

        </article>


        <nav class="text-right">
            <a href="#" @click.prevent="handlerNew" v-show="creatable" class="oscar-link">
                <i class="icon-calendar-plus-o"></i>
                Nouveau type d'organisation
            </a>
        </nav>

    </section>

</template>
<script>

    export default {
        props: ['url', 'creatable'],

        components: {

        },

        data() {
            return {
                organizationtypes: [],
                error: null,
                pendingMsg: "",
                formData: null,
                loading: false,
                creatable: false
            }
        },

        computed: {
            sorted(){
                let types = [];
                for( let i=0; i<this.organizationtypes.length; i++ ){

                }
            }
        },

        methods: {
            getTypeById(id){

            },

            remove(type){
                let promise = this.$http.delete(this.url+'/'+type.id);

                promise.then(
                    success => {
                        this.getOrganizationtypes();
                    },
                    fail => {
                        this.error = fail.body;
                    }
                ).then(f=>{
                    this.pendingMsg = "";
                    this.formData = null;
                })
            },

            edit(type){
                this.formData = type;
            },

            save(){
                this.pendingMsg = "Enregistrement";

                let promise = this.$http.post(this.url, this.formData);

                promise.then(
                    success => {
                        this.getOrganizationtypes();
                    },
                    fail => {
                        this.error = fail.body;
                    }
                ).then(f=>{
                    this.pendingMsg = "";
                    this.formData = null;
                })
            },

            handlerNew(){
                this.formData = {
                    id: "",
                    label: "",
                    description: "",
                    root_id: ""

                };
            },

            /**
             * Chargement des jalons depuis l'API
             */
            getOrganizationtypes() {
                this.pendingMsg = "Chargement des types d'organisation : " + this.url;

                this.$http.get(this.url).then(
                    success => {
                        this.organizationtypes = success.data.organizationtypes;
                       console.log("SUCCESS", success);
                    },
                    error => {
                        this.error = "Impossible de charger les types d'oganisations : " + error.body
                    }
                ).then(n => { this.pendingMsg = ""; });
            }
        },

        mounted() {
            this.getOrganizationtypes()
        }
    }
</script>