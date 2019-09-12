<template>
    <div class="">
        <transition name="fade">
            <div class="overlay" v-if="selectPerson">
                <div class="overlay-content" style="overflow: visible">
                    <a href="#" class="overlay-closer"  @click="selectPerson=false">X</a>
                    <div>
                        <form action="" method="post" @submit="handlerSubmit">
                        <h1>
                            <span v-if="selectPerson == 'add'">AJOUTER</span>
                            <span v-else-if="selectPerson == 'replace'">REMPLACER</span>
                        </h1>

                            <input type="hidden" name="action" value="flipreferent" />
                            <input type="hidden" name="referent_id" :value="referentId" />
                            <input type="hidden" name="mode" :value="selectPerson" />
                            <input type="hidden" name="person_id" :value="personSelected ? personSelected.id : ''" />

                        <h2 class="h3">Choississez le <span v-if="selectPerson == 'add'">suppléant</span><span v-else>remplaçant</span></h2>
                        <personselector @change="handlerSelectPerson"/>

                        <hr>
                        <h2 class="h3">Récapitulatif<span v-if="!personSelected">&hellip;</span></h2>

                        <transition name="fade">
                            <div v-if="personSelected">
                                <p v-if="selectPerson == 'add'">Ajouter <strong>{{ personSelected.displayname }}</strong> comme <strong>suppléant</strong> de <strong>{{ displayName }}</strong></p>
                                <div v-else-if="selectPerson == 'replace'">
                                    <p>Remplacer <strong>{{ displayName }}</strong> par <strong>{{ personSelected.displayname }}</strong></p>
                                    <div class="alert alert-danger">
                                        <i class="icon-attention-1"></i> <strong>{{ displayName }}</strong> ne pourra plus valider les prochaines déclarations Hors-Lot et de celle en attente de validation.
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h3><i class="icon-group"></i>Personnes à valider</h3>
                                        <p class="alert alert-info"> <i class="icon-info-circled"></i> <strong>{{ personSelected.displayname }}</strong> aura en charge de valider les <strong>déclarations Hors-Lot</strong> des personnes suivantes :
                                        </p>
                                        <ul>
                                            <li v-for="p in declarationsFor"><i class="icon-Person"></i> {{ p }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h3><i class="icon-calendar"></i>Validations en cours</h3>
                                        <p class="alert alert-info"><i class="icon-info-circled"></i>  <strong>{{ personSelected.displayname }}</strong> sera autorisé à valider les déclarations <strong>Hors-lot</strong> en cours pour les déclarants : </p>
                                        <ul>
                                            <li v-for="p in declarationsToDo"><i class="icon-Person"></i> {{ p }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </transition>
                        <hr>
                        <div class="btn-group-sm btn-group text-center">
                            <button class="btn btn-sm btn-danger" type="cancel" @click="personSelected = null; selectPerson = null">
                                <i class="icon-block-outline"></i>
                                Annuler</button>
                            <button class="btn btn-sm btn-success" type="submit" :class="personSelected ? '':'disabled'">
                                <i class="icon-valid"></i>
                                Appliquer</button>
                        </div>
                        </form>

                    </div>
                </div>
            </div>
        </transition>
        Vous pouvez :
        <div class="btn-group-sm btn-group">
            <a href="#" @click="selectPerson='replace'" class="btn btn-sm btn-danger"><i class="icon-cw-outline"></i>Remplacer</a>
            <a href="#" @click="selectPerson='add'" class="btn btn-sm btn-primary"><i class="icon-plus-circled"></i>Renforcer</a>
        </div>

    </div>

</template>
<script>
    // nodejs ./node_modules/.bin/poi watch --format umd --moduleName  Referent --filename.js Referent.js --dist public/js/oscar/dist public/js/oscar/src/Referent.vue

    import PersonAutoCompleter from "./PersonAutoCompleter";

    export default {

        components: {
            personselector: PersonAutoCompleter
        },

        props: {
            // Nom du référent
            displayName: { require: true },

            // Identifiant du référent
            referentId: { require: true },

            // Liste des noms des déclarants pour
            // lesquels des déclarations sont en cours
            declarationsToDo: [],

            // Liste des personnes à valider
            declarationsFor: []

        },

        data(){
            return {
                // Mode de selection (Ajout/Remplacement)
                selectPerson: false,

                // Personne selectionnée
                personSelected: null,

                error: null
            }
        },

        methods:{
            /**
             * Quand une personne est selectionnée.
             * @param person
             */
            handlerSelectPerson(person){
                console.log(arguments);
                this.personSelected = person;
            },

            handlerSubmit(evt){
                if( !this.personSelected ){
                    evt.stopPropagation();
                    evt.preventDefault();
                    return false;
                }
            }
        }
    }
</script>