

import Vue from 'vue';
import VueResource from 'vue-resource';
import Bootbox from 'bootbox';

Vue.use(VueResource);

Vue.http.options.emulateJSON = true;
Vue.http.options.emulateHTTP = true;

var Typedocument = Vue.extend({
    template:`
        <section>
            <div class="vue-loader" v-if="loading">
                <span> {{ loadingMsg }}</span>
            </div> 
            <transition name="popup">
                <div class="form-wrapper" v-if="form">
                    <form action="" @submit.prevent="save" class="container oscar-form">
                        <header>
                            <h1>
                                <span v-if='form-id'>Modification de <strong>{{ form.label }}</strong></span>
                                <span v-else>Nouveau type de documents</span> 
                            </h1>
                        </header>
                        <div class="form-group">
                            <label>Nom du type de document</label>
                            <input id='typedoc_label' type="text" class="form-control" v-model="form.label" name="label"/>                  
                        </div>
                        <footer class="buttons-bar">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon-floppy"></i>
                                    Enregistrer
                                </button>
                                <button type="submit" class="btn btn-default" @click="form=null">
                                    <i class="icon-floppy"></i>
                                    Annuler
                                </button>
                            </div>
                        </footer>
                    </form>
                </div>
            </transition> 
            <!-- Vue principale pour les types de documents -->
            <article v-for="typedoc in types" class="card xs">
                <h1 class="card-title">
                    <span>
                        {{ typedoc.label }}
                    </span>
                </h1>
                <nav class="card-footer" v-if="manage">
                    <button class="btn btn-xs btn-primary" @click="form=JSON.parse(JSON.stringify(typedoc))">
                        <i class="icon-pencil"></i>
                    Éditer
                    </button>
                    <button class="btn btn-xs btn-default" @click="remove(typedoc)">
                        <i class="icon-trash"></i>
                    Supprimer
                    </button>
                </nav>
            </article>
            <button @click="formNew" class="btn btn-primary" v-if="manage">
            <i class="icon-circled-plus"></i>
                Ajouter 
            </button>
        </section>      
    `,
    data(){
        return {
            types: [],
            loadingMsg: null,
            form: null,
            manage: false
        }
    },
    computed: {
        loading() {
            return this.loadingMsg != null;
        }
    },
    methods: {
        formNew() {
            this.form = {
                label: "",
                description: ""
            }
        },
        save(){
            if( this.form.id ){
                this.loadingMsg = "Mise à jour du type de document...";
                this.$http.put(this.form.id+"", this.form).then(
                    (res)=>{
                        console.log(res.body, this.form);
                        for( let i=0; i<this.types.length; i++ ){
                            if( this.types[i].id == this.form.id ){
                                this.types.splice(i, 1, res.body);
                                flashMessage('success', 'le type de document a bien été mis à jour.');
                            }
                        }
                    },
                    (err)=>{
                        flashMessage('error', err.body);
                    }
                ).then(()=> { this.loadingMsg = null; this.form = null; });
            }
            else {
                this.loadingMsg = "Ajout d'un nouveau type de document...";
                this.$http.post('',this.form).then(
                    (res)=>{
                        this.types.push(res.body);
                        flashMessage('success', 'le type de document a bien été ajouté.');
                    },
                    (err)=>{
                        flashMessage('error', err.body);
                    }
                ).then(()=> { this.loadingMsg = null; this.form = null; });
            }
        },
        remove(typedoc) {
            Bootbox.confirm("Êtes-vous sûr de supprimer : " + typedoc.label + "?", (res)=> {
                if ( !res ) return;
                this.loadingMsg = "Suppression du type de document...";
                this.$http.delete(typedoc.id + '',this.form).then(
                    (res)=>{
                        this.types.splice(this.types.indexOf(typedoc), 1);
                        flashMessage('success', 'le type de document a bien été supprimé.');
                    },
                    (err)=>{
                        flashMessage('error', err.body);
                    }
                ).then(()=> {this.loadingMsg = null, this.form = null;});
            })
        },
        fetch() {
            this.loadingMsg = "Chargement des types de documents";
            this.$http.get().then(
                (res)=>{
                    this.types = res.body;
                }, (err)=>{
                    flashMessage('error',err.body);
                }).then(()=> this.loadingMsg = null);

        }
    },
    created() {
        this.fetch();
    }
});

export default Typedocument;
