/**
 * Created by jacksay on 17-01-20.
 */
import Vue from 'vue';
import VueResource from 'vue-resource';
import LocalDB from 'LocalDB';
import Bootbox from 'bootbox';
import moment from 'moment-timezone';

Vue.use(VueResource);

Vue.http.options.emulateJSON = true;
Vue.http.options.emulateHTTP = true;

var WorkpackagePerson = {
  template: `<article class="workpackage-person">
                <div class="displayname">
                    <strong>{{ person.person.displayname }}</strong>
                </div>
                <div class="tempsdeclare temps">
                    <div v-if="editable && mode == 'edit'">
                        Heures prévues :
                        <input type="integer" v-model="durationForm" style="width: 5em" @keyup.13="handlerUpdate"/>
                        <a href="#" @click.prevent="handlerUpdate" title="Appliquer la modification des heures prévues"><i class="icon-floppy"></i></a>
                        <a href="#" @click.prevent="handlerCancel" title="Annuler la modification des heures prévues"><i class="icon-cancel-outline"></i></a>
                    </div>
                    <span v-else>
                        <strong >{{person.hours}}/{{ person.duration }}</strong> heure(s)
                    </span>
                    <a href="#" @click.prevent="handlerEdit" v-if="editable && mode == 'read'" title="Modifier les heures prévues"><i class="icon-pencil"></i></a>
                </div>
                <a href="#" @click.prevent="handlerRemove(person)" class="link" v-if="editable && mode == 'read'"><i class="icon-trash"></i> Retirer</a>
            </article>`,
    props: {
        'person': { default: function(){ return {} } },
        'editable': false
    },
    computed: {
        duration(){
            return this.person.duration;
        }
    },
    data(){
      return {
          'canSave': false,
          'mode' : 'read',
          'durationForm': 666
      }
    },
    methods: {
        handlerKeyUp(){
          console.log(arguments);
        },
        handlerUpdate(){
            this.$emit('workpackagepersonupdate', this.person, this.durationForm);
            this.mode = 'read';
        },
        handlerEdit(){
            this.mode = 'edit';
            this.durationForm = this.person.duration;
        },
        handlerCancel(){
            this.mode = 'read';
            this.durationForm = this.person.duration;
        },
        handlerRemove(){
            this.$emit('workpackagepersondelete', this.person);
        }
    }
};

var Workpackage = {
    components: {
        workpackageperson: WorkpackagePerson
    },
    template: `<article class="workpackage">
        <form action="" @submit.prevent="handlerUpdateWorkPackage" v-if="mode == 'edit'">
            <h4><span v-if="workpackage.id > 0">Modification du lot</span><span v-else>Nouveau lot</span> {{ formData.label }}</h4>
            <div class="form-group">
                <label for="">Intitulé</label>
                <input type="text" placeholder="Intitulé" v-model="formData.label" class="form-control" />
            </div>
            <div class="form-group">
                <label for="">Code</label>
                <p class="help">Le code est utilisé pour l'affichage des créneaux</p>
                <input type="text" placeholder="Intitulé" v-model="formData.code" class="form-control" />
            </div>
            
            <div class="form-group" style="display: none">
                <label for="">Période</label>
                <div class="row">
                    <div class="col-md-6">
                        du <input type="date" placeholder="Début" v-model="formData.start" class="form-control" />

                    </div>
                    <div class="col-md-6">
                        du <input type="date" placeholder="Fin" v-model="formData.end" class="form-control" />
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="">Description</label>
                <textarea type="text" placeholder="Description" v-model="formData.description" class="form-control"></textarea>
            </div>
            <div class="buttons">
                <button type="submit" class="btn btn-default">Enregistrer</button>
                <button type="button" class="btn btn-default" @click="handlerCancelEdit">Annuler</button>
            </div>

        </form>
        <div v-if="mode == 'read'">
            <h3>[{{ workpackage.code }}] {{ workpackage.label }}</h3>
            <small style="display: none">Du
                <strong v-if="!workpackage.start">début de l'activité</strong>
                <strong v-else>{{ workpackage.start }}</strong>
                au
                <strong v-if="!workpackage.end">fin de l'activité</strong>
                <strong v-else>{{ workpackage.end }}</strong>
            </small>
            <p>{{ workpackage.description }}</p>

            <section class="workpackage-persons">
                <h4><i class="icon-calendar"></i>Déclarants </h4>
                <workpackageperson v-for="person in workpackage.persons"
                    :person="person"
                    :editable="editable"
                    @workpackagepersondelete="handlerDelete"
                    @workpackagepersonupdate="handlerUpdate"></workpackageperson>
            </section>

            <div class="buttons" v-if="editable">
                <div class="btn-group">
                    <button type="button" class="btn btn-default  btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Ajouter un déclarant <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li v-for="person in persons"><a href="#" @click.prevent="$emit('addperson', person.id, workpackage.id)">{{ person.displayname }}</a></li>
                    </ul>
                </div>
                <a href="#" class="btn btn-default btn-xs" @click.prevent="handlerEditWorkPackage"><i class="icon-pencil"></i>Modifier</a>
                <a href="#" class="btn btn-default btn-xs" @click.prevent="handlerDeleteWorkPackage"><i class="icon-trash"></i>Supprimer</a>
            </div>
        </div>
    </article>`,
    data(){
      return {
          mode: "read",
          canSave: false,
          formData: {
              id: -1,
              code: "",
              label : "",
              description: "",
              start: moment().format(),
              end: moment().format()
          }
      }
    },
    created(){
      console.log("created", this.workpackage.id);
        if( this.workpackage.id < 0 ){
            this.mode = "edit";
        }
    },
    props: {
        'workpackage': null,
        'persons': { default: function(){ return [] } },
        'editable': false,
        'isValidateur': false
    },

    watch: {
        'person.duration': function(){
            console.log('Modification de la durée')
        }
    },

    methods: {
        handlerEditWorkPackage(){
            this.formData = JSON.parse(JSON.stringify(this.workpackage));
            this.mode = 'edit';
        },

        handlerCancelEdit(){
            if( this.workpackage.id < 0 ){
                this.$emit('workpackagecancelnew', this.workpackage);
            } else {
                this.mode = 'read';
            }
        },

        handlerDeleteWorkPackage(){
            Bootbox.confirm("Souhaitez-vous supprimer ce lot ?", (result) => {
                if( result ) this.$emit('workpackagedelete', this.workpackage);
            });
        },

        handlerUpdateWorkPackage(){
            this.$emit('workpackageupdate', this.formData);
            this.mode = 'read';
        },

        handlerUpdate(person, duration){
            this.$emit('workpackagepersonupdate', person, duration);
        },

        handlerDelete(person){
            Bootbox.confirm("Souhaitez-vous supprimer cette personne de la liste des déclarants ?", (result) => {
                if( result ) this.$emit('workpackagepersondelete', person);
            });
        },

        roles(person){
            return person.roles.join(',');
        },

        tempsPrevu(person){
            return 0;
        },

        tempsDeclare(person){
            return 0;
        }

    }
}

var Workpackageperson = Vue.extend({
    components: {
        'workpackage': Workpackage
    },
    template: `<section>
        <transition name="fade">
            <div class="vue-loader" v-if="errors.length">
                <div class="alert alert-danger" v-for="error, i in errors">
                    {{ error }}
                    <a href="" @click.prevent="errors.splice(i,1)"><i class="icon-cancel-outline"></i></a>
                </div>
            </div>
        </transition>

        <div class="vue-loader-component" v-if="loading">
            <span>Chargement</span>
        </div>

        

        <nav class="buttons">
            <a href="" class="btn btn-primary" @click.prevent="handlerWorkPackageNew" v-if="editable">Nouveau lot</a>
        </nav>

        <section class="workpackages">
            <workpackage v-for="wp in workpackages"
                :workpackage="wp"
                :persons="persons"
                :editable="editable"
                :is-validateur="isValidateur"
                @addperson="addperson"
                @workpackageupdate="handlerWorkPackageUpdate"
                @workpackagepersonupdate="handlerUpdateWorkPackagePerson"
                @workpackagepersondelete="handlerWorkPackagePersonDelete"
                @workpackagedelete="handlerWorkPackageDelete"
                @workpackagecancelnew="handlerWorkPackageCancelNew"
                ></workpackage>
        </section>
    </section>`,

    data(){
        return {
            loading: false,
            errors: [],
            workpackages: [],
            persons: [],
            editable: false,
            isDeclarant: false,
            isValidateur: false,
            token: 'DEFAULT_TKN'
        }
    },

    watch: {
    },
    computed: {
    },

    created () {
        Vue.http.interceptors.push((request, next) => {
           request.headers.set('X-CSRF-TOKEN', this.token);
           request.headers.set('Authorization', 'OSCAR TOKEN');
            next();
        });
        this.fetch();
    },

    methods: {
        ////////////////////////////////////////////////////////////////////////
        // HANDLER
        handlerWorkPackageCancelNew(workpackage){
            this.workpackages.splice(this.workpackages.indexOf(workpackage), 1);
        },


        handlerWorkPackageNew(){
            this.workpackages.push({
                    id: -1,
                    code: "Nouveau Lot",
                    label : "",
                    persons: [],
                    description: "",
                    start: moment().format(),
                    end: moment().format()

            })
        },

        handlerWorkPackagePersonDelete(workpackageperson){
            this.$http.delete(this.$http.$options.root+"?workpackagepersonid=" + workpackageperson.id).then(
                (res) => {
                    this.fetch();
                },
                (err) => {
                    this.errors.push("Impossible de supprimer le déclarant : " + err.body);
                }
            );
        },

        handlerWorkPackageDelete(workpackage){
            this.$http.delete(this.$http.$options.root+"?workpackageid=" + workpackage.id).then(
                (res) => {
                    this.fetch();
                },
                (err) => {
                    this.errors.push("Impossible de supprimer le lot : " + err.body);
                }
            );
        },

        handlerWorkPackageUpdate(workPackageData){
            var datas = new FormData();
            for( var key in workPackageData ){
                datas.append(key, workPackageData[key]);
            }
            if( workPackageData.id > 0 ){
                // Mise à jour
                datas.append('workpackageid', workPackageData.id);
                this.$http.post(this.$http.$options.root, datas).then(
                    (res) => {
                        this.fetch();
                    },
                    (err) => {
                        this.errors.push("Impossible de mettre à jour les heures prévues : " + err.body);
                    }
                );
            } else {
                datas.append('workpackageid', -1);
                this.$http.put(this.$http.$options.root, datas).then(
                    (res) => {
                        this.fetch();
                    },
                    (err) => {
                        this.errors.push("Impossible de créer le lot de travail : " + err.body);
                    }
                );
            }
        },

        handlerUpdateWorkPackagePerson(workpackageperson, duration){
            var datas = new FormData();
            datas.append('workpackagepersonid', workpackageperson.id);
            datas.append('duration', duration);
            this.$http.post(this.$http.$options.root, datas).then(
                (res) => {
                    workpackageperson.duration = duration;
                },
                (err) => {
                    this.errors.push("Impossible de mettre à jour les heures prévues : " + err.body);
                }
            );
        },

        addperson(personid, workpackageid){
          console.log(arguments);
            var data = new FormData();
            data.append('idworkpackage', workpackageid);
            data.append('idperson', personid);

            this.$http.put(this.$http.$options.root, data).then(
                (res) => {
                    this.fetch();
                },
                (err) => {
                    this.errors.push("Impossible d'ajouter le déclarant : " + err.body);
                }
            ).then(()=> this.loading = false );
        },

        fetch(){
            this.loading = true;

            this.$http.get(this.$http.$options.root).then(
                (res) => {
                    this.workpackages = res.body.workpackages;
                    this.persons = res.body.persons;
                    this.editable = res.body.editable;
                    this.isDeclarant = res.body.isDeclarant;
                    this.isValidateur = res.body.isValidateur;
                },
                (err) => {
                    this.errors.push("Impossible de charger les lots de travail : " + err.body);
                }
            ).then(()=> this.loading = false );

        }
    }
});

export default Workpackageperson;