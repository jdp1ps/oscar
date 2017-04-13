/**
 * Created by jacksay on 17-01-30.
 */

import Vue from 'vue';
import VueResource from 'vue-resource';
import Bootbox from 'bootbox';

Vue.use(VueResource);

Vue.http.options.emulateJSON = true;
Vue.http.options.emulateHTTP = true;

var OrganizationRole = Vue.extend({
    template: `<section>
    <div class="vue-loader" v-if="loading">
        <span>{{ loadingMsg }}</span>
    </div>
        <transition name="popup">
            <div class="form-wrapper" v-if="form">
                <form action="" @submit.prevent="save" class="container oscar-form">
                    <header>
                        <h1>
                            <span v-if="form.id">Modification de <strong>{{ form.label }}</strong></span>
                            <span v-else>Nouveau rôle</span>
                        </h1>
                        <a href="#" @click="form=null" class="closer">
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                    </header>

                    <div class="form-group">
                      <label>Nom du rôle</label>
                      <input id="role_roleid" type="text" class="form-control" placeholder="Role" v-model="form.label" name="label"/>
                    </div>
                    <div class="form-group">
                      <label>Principal</label>
                      <p class="help">
                        Un rôle définit comme principal sera traité spécifiquement dans l'interface
                      </p>
                      <input type="checkbox" class="form-control" v-model="form.principal"/>
                    </div>

                    <div class="form-group">
                      <label>Description</label>
                      <textarea class="form-control" v-model="form.description"></textarea>
                    </div>

                    <footer class="buttons-bar">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-floppy"></i>
                                Enregistrer
                            </button>
                            <button type="button" class="btn btn-default" @click="form=null">
                                <i class="icon-block"></i>
                                Annuler
                            </button>
                        </div>
                    </footer>
                </form>
            </div>
        </transition>
        <article v-for="role in roles" class="card xs">
            <h1 class="card-title">
                <span>
                    <i v-if="role.principal" class="icon-asterisk"></i>
                    {{ role.label }}
                </span>
            </h1>
            <p>{{ role.description }}</p>
            <nav class="card-footer" v-if="manage">
                <button class="btn btn-xs btn-primary" @click="form=JSON.parse(JSON.stringify(role))">
                    <i class="icon-pencil"></i>
                    Éditer
                </button>
                <button class="btn btn-xs btn-default" @click="remove(role)">
                    <i class="icon-trash"></i>
                    Supprimer
                </button>
            </nav>
        </article>
        <button @click="formNew" class="btn btn-primary" v-if="manage">
          <i class="icon-circled-plus"></i>
           Ajouter un nouveau rôle
        </button>
    </section>`,
    data(){
        return {
            roles: [],
            loadingMsg: null,
            form: null,
            manage: false
        }
    },
    computed: {
        loading(){
            return this.loadingMsg != null;
        }
    },
    methods: {
        formNew(){
            this.form = {
                label: "",
                description: "",
                principal: false
            };
        },
        save(){
            if( this.form.id ){
                this.loadingMsg = "Mise à jour du rôle...";
                this.$http.put(this.form.id+"", this.form).then(
                    (res)=>{
                        console.log(res.body, this.form);
                        for( let i=0; i<this.roles.length; i++ ){
                            if( this.roles[i].id == this.form.id ){
                                this.roles.splice(i, 1, res.body);
                                flashMessage('success', 'le rôle a bien été mis à jour.');
                            }
                        }
                    },
                    (err)=>{
                        flashMessage('error', err.body);
                    }
                ).then(()=> { this.loadingMsg = null; this.form = null; });
            }
            else {
                this.loadingMsg = "Ajout du nouveau rôle...";
                this.$http.post('',this.form).then(
                    (res)=>{
                        this.roles.push(res.body);
                        flashMessage('success', 'le rôle a bien été ajouté.');
                    },
                    (err)=>{
                        flashMessage('error', err.body);
                    }
                ).then(()=> { this.loadingMsg = null; this.form = null; });
            }
        },

        remove(role){
            Bootbox.confirm('Êtes-vous bien sûr de votre coup ?', (res)=> {
                if( !res ) return;
                this.loadingMsg = "Suppression du rôle...";
                this.$http.delete(role.id + '',this.form).then(
                    (res)=>{
                        this.roles.splice(this.roles.indexOf(role), 1);
                        flashMessage('success', 'le rôle a bien été supprimé.');
                    },
                    (err)=>{
                        flashMessage('error', err.body);
                    }
                ).then(()=> { this.loadingMsg = null; this.form = null;});
            })
        },

        fetch(){
            this.loadingMsg = "Chargement des rôles";
            this.$http.get().then(
                (res)=>{
                    this.roles = res.body;
                }, (err)=>{
                    flashMessage('error', err.body);
                }).then(()=> this.loadingMsg = null);
        }
    },
    created(){
        this.fetch();
    }
});

export default OrganizationRole;