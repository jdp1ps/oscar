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
                <div class="overlay-content" style="overflow-y: visible">
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
            <section class="col-md-8">
                <section v-for="line,k in declarations" class="card declaration" @click="line.open = !line.open">
                    <span class="opener">
                        <i class="icon-angle-down" v-if="line.open"></i>
                        <i class="icon-angle-right" v-else></i>
                    </span>
                    <strong>{{ line.person }}</strong> <time>{{ line.period | period }}</time>
                    <span class="validations-icon">
                        <i class="icon" :class="'icon-' +d.status" v-for="d in line.declarations" :title="d.label"></i>
                    </span>
                    <nav>
                        <a href="#" class="btn btn-danger btn-xs" @click.prevent.stop="handlerCancelDeclaration(line)"> <i class="icon-trash"></i>Annuler cette déclaration</a>
                    </nav>
                    <section class="validations text-small" v-show="line.open">
                        <article v-for="validation in line.declarations" class="validation" @click.prevent.stop="selectedValidation = validation" :class="{ 'selected': selectedValidation == validation }">
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
                                <i :class="'icon-' +validation.status"></i>
                            </em>
                        </article>
                    </section>
                </section>
            </section>
            <section class="col-md-4">
                <div v-if="selectedValidation" class="validation-details">
                    <h3>
                        <small>Validation pour les créneaux</small><br>
                        <strong v-if="selectedValidation.object == 'activity'">
                           <i class="icon-cube"></i> {{ selectedValidation.label }}
                        </strong>
                        <strong v-else>
                            <i :class="'icon-' +selectedValidation.object"></i> {{ selectedValidation.label }}
                        </strong><br>
                        <small>
                            de <strong>{{ selectedValidation.person }}</strong>
                            en <strong>{{ selectedValidation.period | period }}</strong>
                        </small>
                    </h3>

                    <div v-if="selectedValidation.object == 'activity'">
                        <!-- Validation niveau projet -->
                        <div v-if="selectedValidation.validation.validationactivity_by" class="card valid">
                            <i class="icon-ok-circled"></i>
                            Validation projet par <strong><i class="icon-user"></i>{{ selectedValidation.validation.validationactivity_by }}</strong> le
                            <time>{{ selectedValidation.validation.validationactivity_at | humandate }}</time>
                        </div>
                        <div v-else-if="selectedValidation.validation.rejectactivity_by" class="card reject">
                            <i class="icon-attention-circled"></i>
                            Rejet des créneaux par <strong><i class="icon-user"></i>{{ selectedValidation.validation.rejectactivity_by }}</strong> le
                            <time>{{ selectedValidation.validation.rejectactivity_at | humandate }}</time> :
                            <pre>{{ selectedValidation.validation.rejectactivity_message }}</pre>
                        </div>
                        <div v-else class="card waiting">
                            <strong>Validation projet en attente</strong>
                            par l'un des validateurs suivant :
                            <ul>
                                <li v-for="p in selectedValidation.validateursPrj">
                                    <i class="icon-user"></i>{{ p.person }}
                                    <a class="link" @click.prevent.stop="handlerDelete('prj', p)"><i class="icon-trash"></i> Supprimer</a>
                                </li>
                            </ul>
                            <a class="btn btn-xs btn-primary" @click.prevent.stop="handlerAdd('prj')">Ajouter un validateur</a>
                        </div>

                        <div v-if="selectedValidation.validation.validationsci_by" class="card valid">
                            Validation scientifique par <strong><i class="icon-user"></i>{{ selectedValidation.validation.validationsci_by }}</strong> le
                            <time>{{ selectedValidation.validation.validationsci_at | humandate }}</time>

                        </div>
                        <div v-else-if="selectedValidation.validation.rejectsci_by" class="card reject">
                            <i class="icon-attention-circled"></i>
                            Rejet scientifique des créneaux par <strong><i class="icon-user"></i>{{ selectedValidation.validation.rejectsci_by }}</strong> le
                            <time>{{ selectedValidation.validation.rejectsci_at | humandate }}</time>
                            <pre>{{ selectedValidation.validation.rejectsci_message }}</pre>
                        </div>
                        <div v-else class="card waiting">
                            <strong>Validation scientifique en attente</strong>
                            par l'un des validateurs suivant :
                            <ul>
                                <li v-for="p in selectedValidation.validateursSci">
                                    <i class="icon-user"></i>{{ p.person }}
                                    <a class="link" @click.prevent.stop="handlerDelete('sci', p)"><i class="icon-trash"></i> Supprimer</a>
                                </li>
                            </ul>
                            <a class="btn btn-xs btn-primary" @click.prevent.stop="handlerAdd('sci')">Ajouter un validateur</a>
                        </div>
                    </div>

                    <div v-if="selectedValidation.validation.validationadm_by" class="card valid">
                        Validation administrative par <strong><i class="icon-user"></i>{{ selectedValidation.validation.validationadm_by }}</strong> le
                        <time>{{ selectedValidation.validation.validationadm_at | humandate }}</time>
                    </div>
                    <div v-else-if="selectedValidation.validation.rejectadm_by" class="card reject">
                        <i class="icon-attention-circled"></i>
                        Rejet administrative des créneaux par <strong><i class="icon-user"></i>{{ selectedValidation.validation.rejectadm_by }}</strong> le
                        <time>{{ selectedValidation.validation.rejectadm_at | humandate }}</time>
                        <pre>{{ selectedValidation.validation.validationadm_message }}</pre>
                    </div>
                    <div v-else class="card waiting">
                        <strong>Validation administrative en attente</strong>
                        par l'un des validateurs suivant :
                        <ul>
                            <li v-for="p in selectedValidation.validateursAdm">
                                <i class="icon-user"></i>{{ p.person }}
                                <a class="link" @click.prevent.stop="handlerDelete('adm', p)"><i class="icon-trash"></i> Supprimer</a>
                            </li>
                        </ul>
                        <a class="btn btn-xs btn-primary" @click.prevent.stop="handlerAdd('adm')">Ajouter un validateur</a>
                    </div>

                    <pre>{{ selectedValidation }}</pre>
                </div>
            </section>
        </div>
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

            handlerDelete( type, person ){

                this.loading = "Suppression du validateur";

                let datas = new FormData();
                datas.append('person_id', person.id);
                datas.append('declaration_id', this.selectedValidation.id);
                datas.append('action', 'delete');
                datas.append('type', type);

                this.$http.post('', datas).then(
                    ok => {

                        switch( type ){
                            case "prj":
                                this.selectedValidation.validateursPrj.splice(this.selectedValidation.validateursPrj.indexOf(person, 1));
                                break;
                            case "sci":
                                this.selectedValidation.validateursSci.splice(this.selectedValidation.validateursSci.indexOf(person, 1));
                                break;
                            case "adm":
                                this.selectedValidation.validateursAdm.splice(this.selectedValidation.validateursAdm.indexOf(person, 1));
                                break;
                        }
                    },
                    ko => {
                        this.error = AjaxResolve.resolve("Impossible de supprimer ce validateur", ko);

                    }
                ).then(foo => {
                    this.addedPerson = null;
                    this.create = null;
                    this.loading = false
                });
            },

            handlerConfirmAdd(type, personId){

                this.loading = "Ajout du validateur";

                let datas = new FormData();
                datas.append('person_id', personId);
                datas.append('declaration_id', this.selectedValidation.id);
                datas.append('type', type);

                this.$http.post('', datas).then(
                    ok => {
                        switch( type ){
                            case "prj":
                                this.selectedValidation.validateursPrj.push(ok.body);
                                break;
                            case "sci":
                                this.selectedValidation.validateursSci.push(ok.body);
                                break;
                            case "adm":
                                this.selectedValidation.validateursAdm.push(ok.body);
                                break;
                        }
                    },
                    ko => {
                        this.error = AjaxResolve.resolve("Impossible d'ajouter le validateur", ko);
                    }
                ).then(foo => {
                    this.addedPerson = null;
                    this.create = null;
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