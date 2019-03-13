<template>


    <div style="position: relative">

        <div class="overlay">
            <div class="overlay-content">
                <p>{{ deleteQuestion }}
                <button @click=""></button>
            </div>
        </div>

        <div class="relative-overlay" v-if="loading">
            <div class="overlay-content" style="">
                Chargement des membres
            </div>
        </div>
        <span class="cartouche" v-for="p in persons" :class="{ 'primary': p.rolePrincipal }">
            <i class="icon-cube" v-if="p.context == 'activity'"></i>
            <i class="icon-cubes" v-else></i>
            {{ p.enrolledLabel }}
            <span class="addon">
                {{ p.roleLabel }}

                <a href="#" v-if="p.editable" @click="handlerEdit(p)"><i class="icon-edit"></i></a>
                <a href="#" v-if="p.deletable" @click="handlerDelete(p)"><i class="icon-trash"></i></a>
            </span>
        </span>
    </div>

</template>
<script>

    // nodejs node_modules/.bin/poi watch --format umd --moduleName  ActivityPersons --filename.js ActivityPersons.js --dist public/js/oscar/dist public/js/oscar/src/ActivityPersons.vue


    /*    "id": 3,
      "role": "Ingénieur",
      "roleLabel": "Ingénieur",
      "rolePrincipal": false,
      "urlDelete": "/personnactivity/delete/3",
      "context": "activity",
      "urlEdit": "/personnactivity/edit/3",
      "enroller": 1,
      "enrollerLabel": "Exemple d'activité 1",
      "editable": true,
      "deletable": true,
      "enrolled": 10,
      "enrolledLabel": "Sarah Déclarant",
      "start": null,
      "end": null

     */
    import OscarComponent from './OscarComponent.js';

    export default {
        mixins: [OscarComponent],
        data(){
            return {
                persons: [],
                loading: ""
            }
        },
        methods: {
            handlerEdit(p){

            },

            handlerDelete(p){
              this.deleteConfirmation("Supprimer " + p.enrolledLabel + "?");
            },
            onOk(response){
                this.persons = response.body;
            }
        },
        mounted(){

            this.fetch(this.onOk);
        }
    }

</script>