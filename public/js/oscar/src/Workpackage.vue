<template>
    <article class="workpackage">
        <form action="" @submit.prevent="handlerUpdateWorkPackage" v-if="mode == 'edit'">

            <h4><span v-if="workpackage.id > 0">Modification du lot</span><span v-else>Nouveau lot</span> {{ formData.label }}</h4>

            <div class="form-group">
                <label for="">Code</label>
                <p class="text-danger">Le code est <strong>utilisé pour l'affichage des créneaux</strong> simplifiés, utilisez un code de préférence entre 3 et 5 caractères.</p>
                <input type="text" placeholder="CODE" v-model="formData.code" class="form-control" />
            </div>

            <div class="form-group">
                <label for="">Intitulé</label>
                <input type="text" placeholder="Intitulé" v-model="formData.label" class="form-control" />
            </div>

            <div class="form-group">
                <label for="">Description</label>
                <textarea type="text" placeholder="Description" v-model="formData.description" class="form-control"></textarea>
            </div>
            <div class="buttons">
                <button type="submit" class="btn btn-default" :class="{'disabled': !formData.code }">Enregistrer</button>
                <button type="button" class="btn btn-default" @click="handlerCancelEdit">Annuler</button>
            </div>

        </form>
        <div v-if="mode == 'read'">
            <h3>[{{ workpackage.code }}] {{ workpackage.label }}</h3>

            <p>{{ workpackage.description }}</p>

            <section class="workpackage-persons">
                <h4><i class="icon-calendar"></i>Déclarants </h4>
                <workpackageperson v-for="person in workpackage.persons"
                                   :key="person.id"
                                   :person="person"
                                   :editable="editable"
                                   @workpackagepersondelete="handlerDelete"
                                   @workpackagepersonupdate="handlerUpdate"></workpackageperson>
            </section>

            <div class="buttons" v-if="editable && persons.length">
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
            <div class="text-danger" v-if="persons.length <= 0">
                Vous n'avez pas encore ajouté de membre à cette activité. <strong>Seul les membres d'une activité peuvent être identifiés comme déclarant</strong>.
            </div>
        </div>
    </article>
</template>
<script>

    // poi watch --format umd --moduleName  Workpackage --filename.js Workpackage.js --dist public/js/oscar/dist public/js/oscar/src/Workpackage.vue

    export default {
        components: {
            'workpackageperson' : require('./WorkpackagePerson.vue').default
        },

        data(){
            return {
                mode: "read",
                canSave: false,
                formData: {
                    id: -1,
                    code: "",
                    label : "",
                    description: ""
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
                this.$emit('workpackagedelete', this.workpackage);
            },

            handlerUpdateWorkPackage(){
                this.$emit('workpackageupdate', this.formData);
                this.mode = 'read';
            },

            handlerUpdate(person, duration){
                this.$emit('workpackagepersonupdate', person, duration);
            },

            handlerDelete(person){
                this.$emit('workpackagepersondelete', person);
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
</script>