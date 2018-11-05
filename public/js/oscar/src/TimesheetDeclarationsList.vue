<template>
    <section class="validations-admin">
        <transition name="fade">
            <div class="pending overlay" v-if="loading">
                <div class="overlay-content">
                    <i class="icon-spinner animate-spin"></i>
                    {{ loading }}
                </div>
            </div>
        </transition>
        <transition name="fade">
            <div class="pending overlay" v-if="error">
                <div class="overlay-content">
                    <i class="icon-attention-1"></i>
                    {{ error }}
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="overlay" v-show="create">
                <div class="overlay-content">
                    <span class="overlay-closer" @click="create = null">X</span>
                    Choisissez une personne à ajouter :
                    <personautocompleter @change="handlerAddPerson"/>

                    <button @click="handlerConfirmAdd(create, addedPerson.id)" :class="{ 'disabled' : addedPerson == null }" class="btn btn-primary">
                        <span v-if="addedPerson != null">Ajouter <strong>{{ addedPerson.displayname }}</strong> comme validateur</span>
                        <span v-else>Selectionner une personne</span>
                    </button>
                </div>
            </div>
        </transition>

        <h1>Liste des déclarations</h1>

        <div class="row">
            <section v-for="line,k in declarations" class="card declaration col-md-8" @click="line.open = !line.open">
            <strong>{{ line.person }}</strong> <time>{{ line.period }}</time>

            <span class="validations-icon">
                <i class="icon" :class="'icon-' +d.status" v-for="d in line.declarations" :title="d.label"></i>
            </span>
            <nav>
                <a href="#" class="btn btn-danger btn-xs" @click.prevent.stop="handlerCancelDeclaration(line)"> <i class="icon-trash"></i>Annuler cette déclaration</a>
            </nav>
            <section class="validations text-small" v-show="line.open">
                <article v-for="validation in line.declarations" class="validation" @click.prevent.stop="selectedValidation = validation">

                    <span>
                        <i :class="validation.object == 'activity' ? 'icon-cube' : 'icon-tag'"></i>
                        <strong>{{ validation.label }}</strong>
                    </span>

                    <span v-if="validation.object == 'activity'">
                        <span v-if="validation.validation.validationactivity_by" class="cartouche green" title="Validation projet">
                            <i class="icon-cube"></i>{{ validation.validation.validationactivity_by }}
                        </span>
                        <span v-else v-else class="validators">
                            <i class="icon-cube"></i>
                            <span v-for="p in validation.validateursPrj">{{ p.person }}</span>
                        </span>
                    </span>
                    <span v-else>~</span>

                    <span v-if="validation.object == 'activity'">
                        <span v-if="validation.validation.validationsci_by" class="cartouche green" title="Validation scientifique">
                            <i class="icon-beaker"></i>{{ validation.validation.validationsci_by }}
                        </span>
                        <span v-else class="validators">
                            <i class="icon-beaker"></i>
                            <span v-for="p in validation.validateursSci">{{ p.person }}</span>
                        </span>
                    </span>
                    <span v-else>~</span>

                    <span>
                        <span v-if="validation.validation.validationadm_by" class="cartouche green" title="Validation administrative">
                            <i class="icon-book"></i>{{ validation.validation.validationadm_by }}
                        </span>
                        <span v-else class="validators">
                            <i class="icon-book"></i>
                            <span v-for="p in validation.validateursAdm">{{ p.person }}</span>
                        </span>
                    </span>
                    <em>
                        État : <i :class="'icon-' +validation.status"></i>
                    </em>
                </article>
            </section>
        </section>
            <section class="col-md-4">
                <div v-if="selectedValidation">
                    <h3>
                        Validation pour les créneaux
                        <strong v-if="selectedValidation.object == 'activity'">
                           <i class="icon-cube"></i> {{ selectedValidation.label }}
                        </strong>
                        <strong v-else>
                            <i :class="'icon-' +selectedValidation.object"></i> {{ selectedValidation.label }}
                        </strong>
                    </h3>

                    <div v-if="selectedValidation.object == 'activity'">

                        <div v-if="selectedValidation.validation.validationactivity_by" class="card">
                            Validation projet par <strong><i class="icon-user"></i>{{ selectedValidation.validation.validationactivity_by }}</strong> le
                            <time>{{ selectedValidation.validation.validationactivity_at | humandate }}</time>
                        </div>
                        <div v-else class="card">
                            <strong>Validation projet en attente</strong>
                            par l'un des validateurs suivant :
                            <ul>
                                <li v-for="p in selectedValidation.validateursPrj"><i class="icon-user"></i>{{ p.person }}</li>
                            </ul>
                        </div>

                        <div v-if="selectedValidation.validation.validationsci_by" class="card">
                            Validation scientifique par <strong><i class="icon-user"></i>{{ selectedValidation.validation.validationsci_by }}</strong> le
                            <time>{{ selectedValidation.validation.validationsci_at | humandate }}</time>
                        </div>
                        <div v-else class="card">
                            <strong>Validation scientifique en attente</strong>
                            par l'un des validateurs suivant :
                            <ul>
                                <li v-for="p in selectedValidation.validateursSci"><i class="icon-user"></i>{{ p.person }}</li>
                            </ul>
                        </div>

                    </div>

                    <div v-if="selectedValidation.validation.validationadm_by" class="card">
                        Validation administrative par <strong><i class="icon-user"></i>{{ selectedValidation.validation.validationadm_by }}</strong> le
                        <time>{{ selectedValidation.validation.validationadm_at | humandate }}</time>
                    </div>
                    <div v-else class="card">
                        <strong>Validation administrative en attente</strong>
                        par l'un des validateurs suivant :
                        <ul>
                            <li v-for="p in selectedValidation.validateursAdm">
                                <i class="icon-user"></i>{{ p.person }}
                                <a class="link" @click.prevent.stop="handlerDelete('adm', p.id)">Supprimer</a>
                            </li>
                        </ul>

                        <a class="btn btn-xs btn-primary" @click.prevent.stop="handlerAdd('adm')">Ajouter un validateur</a>
                    </div>


                    <pre>{{ selectedValidation }}</pre>
                </div>
            </section>
        </div>
        <pre>TOT :{{ $data }}</pre>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  TimesheetDeclarationsList --filename.css TimesheetDeclarationsList.css --filename.js TimesheetDeclarationsList.js --dist public/js/oscar/dist public/js/oscar/src/TimesheetDeclarationsList.vue
    import AjaxResolve from "./AjaxResolve";
    import PersonAutoCompleter from "./PersonAutoCompleter";



    export default {
        name: 'TimesheetDeclarationsList',

        props: {
            moment: {required: true},
            bootbox: {required: true},
            urlapi: {default: null},
            editable: { default: false }
        },

        components: {
            'personautocompleter': PersonAutoCompleter
        },

        data() {
            return {
                loading: null,
                declarations: [],
                error: null,
                selectedValidation: null,
                create: false,
                addedPerson: null
            }
        },

        filters: {

        },

        methods: {

            handlerAdd(type){
                console.log('Type:', type);
                this.create = type;
            },

            handlerAddPerson(data){
              console.log("ajout", data);
              this.addedPerson = data;
            },

            handlerConfirmAdd(type, personId){

                this.loading = "Ajout du validateur";

                let datas = new FormData();
                datas.append('person_id', personId);
                datas.append('declaration_id', this.selectedValidation.id);
                datas.append('type', type);

                this.$http.post('', datas).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = AjaxResolve.resolve("Impossible d'ajouter le validateur", ko);
                    }
                ).then(foo => {
                    this.loading = false
                });
            },


            fetch(clear = true) {
                this.loading = "Chargement des données";

                this.$http.get('').then(
                    ok => {
                        for( let item in ok.body ){
                            ok.body[item].open = false;
                        }
                        this.declarations = ok.body
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                    }
                ).then(foo => {
                    this.loading = false
                });
            },

            handlerCancelDeclaration(declaration){
                console.log(declaration);
                this.bootbox.confirm("Supprimer la déclaration (le déclarant devra réenvoyer la déclaration) ?", ok => {
                    if( ok ){
                        this.loading = "Suppression de la déclaration";

                        this.$http.delete('?person_id=' + declaration.person_id +"&period=" +declaration.period).then(
                            ok => {
                                this.fetch();
                            },
                            ko => {
                                this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                            }
                        ).then(foo => {
                            this.loading = false
                        });
                    }
                })

            },
        },

        mounted() {
            this.fetch(true)
        }
    }
</script>