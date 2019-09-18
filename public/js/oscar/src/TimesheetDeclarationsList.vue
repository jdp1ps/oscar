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

        <transition name="fade">
            <div class="overlay" v-if="addvalidatorperson">
                <div class="overlay-content" style="overflow-y: visible">
                    <span class="overlay-closer" @click="addvalidatorperson = null">X</span>
                    <h3>Assigner un validateur Hors-lot</h3>
                    <p class="alert alert-info">Selectionnez un validateur pour les <strong>créneaux Hors-Lot</strong> de <strong>{{ filterPerson }}</strong>.
                    Cet opération affectera également le validateur pour les déclarations en cours non-validée.
                    </p>

                    <personautocompleter @change="handlerSelectValidateur" />
                    <form action="" method="post">
                        <input type="hidden" name="action" value="addvalidator" />
                        <input type="hidden" name="person" :value="selectedPerson.id" />
                        <input type="hidden" name="validatorId" :value="validatorId" />
                        <button type="submit" class="btn btn-primary" :class="{ 'disabled' : !validatorId }">Ajouter <strong>{{ validatorLabel }}</strong> comme validateur hors-lot</button>
                    </form>
                    <button @click="addvalidatorperson = null">Annuler</button>
                </div>
            </div>
        </transition>


        <transition name="fade">
            <div class="overlay" v-if="schedule">
                <div class="overlay-content" style="overflow-y: visible">
                    <personschedule :schedule="schedule.schedule"
                                    @cancel="schedule = null"
                                    @changeschedule="handlerSaveSchedule" :editable="true"/>
                </div>
            </div>
        </transition>

        <h1>Liste des déclarations</h1>

        <div class="declarations-ui cols">
            <div class="persons-list col-1">
                <h3><i class="icon-sort"></i> Filtres</h3>
                <h4><i class="icon-group"></i> Déclarants</h4>
                <article v-for="p in declarers" @click.prevent="handlerFilterPerson(p)" class="list-item" :class="{'selected': p.displayname == filterPerson }">
                    <i class="icon-user"></i> {{ p.displayname }}
                    <i class="icon-attention-1" v-if="p.referents.length == 0"></i>
                </article>

                <h4><i class="icon-cubes"></i> Activités</h4>
                <article v-for="a in activities" @click.prevent="handlerFilterActivity(a)" class="list-item" :class="{'selected': a == filterActivity }">
                    <i class="icon-cube"></i> {{ a }}
                </article>
            </div>
            <div class="declarations-list col-4">
                <h2><i class="icon-calendar"></i> Déclaration</h2>

                <!--                                        -->
                <!--       DECLARATION                      -->
                <!--                                        -->
                <section v-for="line,k in filteredDeclarations" class="card declaration" @click="line.open = !line.open">
                    <span class="opener">
                        <i class="icon-angle-down" v-if="line.open"></i>
                        <i class="icon-angle-right" v-else></i>
                    </span>
                    <strong>{{ line.person }}</strong> <time>{{ line.period | period }}</time>
                    <span class="validations-icon">
                        <i class="icon-attention-1 bg-danger rounded" style="border-radius: 8px" v-if="line.warnings.length > 0"></i>
                        <i class="icon" :class="'icon-' +d.status" v-for="d in line.declarations" :title="d.label"></i>
                    </span>

                    <nav>

                        <a :href="'/feuille-de-temps/excel?action=export2&period=' + line.period +'&personid=' + line.person_id" class="btn btn-default btn-xs">
                            <i class="icon-file-pdf"></i>Voir</a>

                        <a href="#" class="btn btn-default btn-xs" @click.prevent.stop="handlerChangeSchedule(line)">
                            <i class="icon-clock"></i>Horaires</a>
                        <a href="#" class="btn btn-danger btn-xs" @click.prevent.stop="handlerCancelDeclaration(line)">
                            <i class="icon-trash"></i>Annuler</a>
                    </nav>

                    <transition name="slide">
                        <section class="validations text-small" v-if="line.open">
                            <ul class="alert-danger alert" v-if="line.warnings.length > 0">
                                <li v-for="w in line.warnings">{{w}}</li>
                            </ul>
                            <article v-for="validation in line.declarations" class="validation" @click.prevent.stop="selectedValidation = validation" :class="{ 'selected': selectedValidation == validation }">
                            <span>
                                <i :class="validation.object == 'activity' ? 'icon-cube' : 'icon-' + validation.object"></i>
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
                    </transition>
                </section>
            </div>
            <div class="declaration-details col-2">
                <div v-if="filterPerson">
                    <h3><i class="icon-cog"></i> {{ filterPerson }}</h3>
                    <div v-if="selectedPerson.referents.length == 0" class="alert alert-danger">
                        Aucun référent pour <strong>valider les déclarations Hors-lot</strong>
                    </div>
                    <div v-else>
                    <h4>Validateur :</h4>
                    <ul>
                        <li v-for="r in selectedPerson.referents" class="cartouche cartouche-default">{{ r.displayname }}</li>
                    </ul>
                    </div>
                    <button class="btn-primary btn" @click="addvalidatorperson = true">
                        Ajouter un validateur pour les créneaux <strong>Hors-Lot</strong>
                    </button>
                    <a class="btn-primary btn" :href="'/person/show/' + selectedPerson.id">
                        Voir la fiche de <strong>{{ filterPerson }}</strong>
                    </a>
                </div>
                <h3><i class="icon-zoom-in-outline"></i>
                    Détails</h3>
                <p class="alert alert-info" v-if="!selectedValidation">
                    Selectionnez une ligne d'une déclaration pour afficher les détails et <strong>gérer les validateurs</strong>
                </p>
                <transition name="fade">
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
                    </div>
                </transition>
            </div>
        </div>


    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  TimesheetDeclarationsList --filename.css TimesheetDeclarationsList.css --filename.js TimesheetDeclarationsList.js --dist public/js/oscar/dist public/js/oscar/src/TimesheetDeclarationsList.vue
    import AjaxResolve from "./AjaxResolve";
    import PersonAutoCompleter from "./PersonAutoCompleter";
    import PersonSchedule from "./PersonSchedule";



    export default {
        name: 'TimesheetDeclarationsList',

        props: {
            moment: {required: true},
            bootbox: {required: true},
            urlapi: {default: null},
            editable: { default: false }
        },

        components: {
            'personautocompleter': PersonAutoCompleter,
            'personschedule': PersonSchedule
        },

        data() {
            return {
                loading: null,
                schedule: null,
                declarations: {},
                declarers: {},
                error: null,
                selectedValidation: null,
                create: false,
                addedPerson: null,
                filterPerson: "",
                filterActivity: "",
                selectedPerson: null,
                validatorId: null,
                validatorLabel: null,
                addvalidatorperson: null
            }
        },

        computed: {
            declarants(){
                let declarants = {};
                if( this.declarations ){
                    Object.keys(this.declarations).forEach( k => {
                        let person = this.declarations[k].person;
                        let person_id = this.declarations[k].person_id;

                        if( !declarants.hasOwnProperty(person) ){
                            declarants[person] = {
                                person: person,
                                person_id: person_id
                            }
                        }
                    })
                }
                return declarants;
            },

            activities(){
                let activities = [];
                if( this.declarations ){
                    Object.keys(this.declarations).forEach( k => {
                        for( let i=0; i<this.declarations[k].declarations.length; i++ ){
                            if( activities.indexOf(this.declarations[k].declarations[i].label) < 0 ){
                                activities.push(this.declarations[k].declarations[i].label)
                            }
                        }
                    })
                }
                return activities;
            },

            filteredDeclarations(){
                let declarations = [];

                let keys = Object.keys(this.declarations);
                for( let i=0; i<keys.length; i++ ){
                    let k = keys[i];
                    let period = this.declarations[k];

                    if( this.filterPerson != "" ){
                        if( period.person != this.filterPerson ){
                            continue;
                        }
                    }

                    if( this.filterActivity != "" ){
                        let keep = false;
                        period.declarations.forEach(v => {
                            if( v.label == this.filterActivity ){
                                keep = true;
                            }
                        });
                        if( keep == false ) continue;
                    }



                    declarations.push(period);
                }

                return declarations;
            }
        },

        methods: {

            /**
             * Enregistrement de la modification de la répartition horaire.
             * @param evt
             */
            handlerSaveSchedule(evt){
                let datas = new FormData();
                datas.append('person_id', this.schedule.person_id);
                datas.append('period', this.schedule.period);
                datas.append('action', 'changeschedule');
                datas.append('days', JSON.stringify(evt));

                this.$http.post('', datas).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = AjaxResolve.resolve("Impossible de modifier les horaires", ko);

                    }
                ).then(foo => {
                    this.addedPerson = null;
                    this.schedule = null;
                    this.create = null;
                    this.loading = false
                });
            },

            handlerChangeSchedule(line){
              console.log(line.settings);
              this.schedule = {
                schedule : JSON.parse(line.settings),
                person_id: line.person_id,
                period: line.period,
              };
            },

            handlerSelectValidateur(validator){
              this.validatorLabel = validator.displayname;
              this.validatorId = validator.id;
            },

            handlerAdd(type){
                this.create = type;
            },

            handlerAddPerson(data){
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
                        for( let item in ok.body.periods ){
                            ok.body.periods[item].open = false;
                        }
                        this.declarations = ok.body.periods;
                        this.declarers = ok.body.declarants;
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
            //////////////////////////////////////
            // Application des filtres d'affichage

            handlerFilterPerson( person ){
                this.selectedValidation = null;
                this.filterPerson = this.filterPerson == person.displayname ? "" : person.displayname;
                this.selectedPerson = person;
            },

            handlerFilterActivity( activity ){
                this.selectedValidation = null;
                this.filterActivity = this.filterActivity == activity ? "" : activity;
            }
        },

        mounted() {
            this.fetch(true)
        }
    }
</script>