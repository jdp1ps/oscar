<template>
    <div class="importer">
        <div class="importer-ui">

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#import-newimport" data-toggle="tab">
                        <i class="icon-calendar"></i>
                        Nouvel import
                    </a>
                </li>
                <li role="presentation">
                    <a href="#import-importslist" data-toggle="tab">
                        <i class="icon-history"></i>
                        Historique des importations
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane" id="import-importslist">
                    <article class="card" v-for="imp in existingIcs">
                        <h3 class="card-title">
                            {{ imp.icsfilename }}, le {{ imp.icsfiledateAdded | moment }}
                        </h3>
                        <small>UID : <strong>{{ imp.icsfileuid }} </strong></small>
                        <nav>
                            <a href="#" @click="$emit('deleteics',imp.icsfileuid)" class="link"><i class="icon-trash"></i> supprimer</a>
                        </nav>
                    </article>
                    <div class="buttons">
                        <button class="btn btn-default" @click="$emit('cancel')">Fermer</button>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane active" id="import-newimport">
                    <h1><i class="icon-calendar"></i>Importer un ICS</h1>
                    <nav class="steps">
                        <span :class="{active: etape == 1}">Fichier ICS</span>
                        <span :class="{active: etape == 2}">Créneaux à importer</span>
                        <span :class="{active: etape == 3}">Finalisation</span>
                    </nav>

                    <section class="etape1 row" v-if="etape == 1">
                        <div class="col-md-1">Du</div>
                        <div class="col-md-5">
                            <datepicker v-model="periodStart" :moment="moment"></datepicker>
                        </div>

                        <div class="col-md-1">au</div>
                        <div class="col-md-5">
                            <datepicker v-model="periodEnd" :moment="moment"></datepicker>
                        </div>
                        <p>Choisissez un fichier ICS : </p>
                        <input type="file" @change="loadIcsFile">
                    </section>

                    <section class="etape2" v-if="etape == 2">
                        <h2><i class="icon-download-outline"></i>Aperçu des données chargées</h2>
                        <p>Voici les données chargées depuis le fichier ICS fournis : </p>
                        <div class="calendar calendar-list">
                            <article v-for="pack in packs">
                                <section class="events">
                                    <h3>{{ pack.label }}</h3>
                                    <section class="events-list">
                                        <eventitemimport :event="event" v-for="event in pack.events" :key="event.id"></eventitemimport>
                                    </section>
                                </section>
                            </article>
                        </div>
                        <div>
                            <h2><i class="icon-loop-outline"></i>Correspondance des créneaux</h2>
                            <input v-model="search" placeholder="Filter les créneaux">
                            <section class="correspondances">
                                <article v-for="label in labels" v-show="!search || (label && label.toLowerCase().indexOf(search.toLowerCase())) >= 0">
                                    <strong><span :style="{'background': background(label)}" class="square">&nbsp</span>{{ label }}</strong>
                                    <select v-model="associations[label]" id="" @change="updateLabel(label, $event.target.value)" class="form-control">
                                        <option value="ignorer">Ignorer ces créneaux</option>
                                        <option value="">Conserver</option>
                                        <option :value="creneau" v-for="creneau in creneaux">Placer dans {{ creneau }}</option>
                                    </select>
                                </article>
                            </section>
                        </div>
                    </section>

                    <div class="buttons">
                        <button class="btn btn-default" @click="$emit('cancel')">Annuler</button>
                        <button class="btn btn-primary" @click="applyImport" v-if="etape==2">
                            Valider l'import de ces créneaux
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import Datepicker from './Datepicker.vue';
    import EventItemImport from './EventItemImport.vue';
    import ICalAnalyser from './ICalAnalyser';
    import EventDT from './EventDT';

    export default {
        props: {
            'store': { require: true },
            'moment': { require: true },
            'creneaux': {
                default: ['test A', 'test B', 'test C']
            }
        },


        data(){
            return {
                periodStart: null,
                periodEnd: null,
                importedEvents: [],
                associations: [],
                labels: [],
                etape: 1,
                search: ""
            }
        },

        filters: {
            moment( str ){
                let m = this.moment(str);
                return m.format('DD MMMM YYYY') + '(' + m.fromNow() +')';
            }
        },

        components: {
            'datepicker': Datepicker,
            'eventitemimport': EventItemImport
        },

        computed: {
            workpackages(){
                return this.store.wps;
            },
            existingIcs(){
                return this.store.ics;
            },
            packs(){
                var packs = [];
                this.importedEvents.forEach(item => {
                    let currentPack = null;
                    let currentLabel = item.mmStart.format('DD MMMM YYYY');
                    for (let i = 0; i < packs.length && currentPack == null; i++) {
                        if (packs[i].label == currentLabel) {
                            currentPack = packs[i];
                        }
                    }
                    if (!currentPack) {
                        currentPack = {
                            label: currentLabel,
                            events: []
                        };
                        packs.push(currentPack);
                    }
                    currentPack.events.push(item);
                });
                return packs;
            }
        },

        methods: {
            background(label){
                return this.colorLabel(label);
            },
            updateLabel(from, to){
                if (to == 'ignorer') {
                    this.importedEvents.forEach(item => {
                        if (item.label == from)
                            item.imported = false;
                    })
                } else if (to == 'conserver') {
                    this.importedEvents.forEach(item => {
                        if (item.label == from)
                            item.useLabel = '';
                        item.imported = true;
                    });
                } else {
                    this.importedEvents.forEach(item => {
                        if (item.label == from) {
                            item.useLabel = to;
                            if( !item.description )
                                item.description = from;
                            item.imported = true;
                        }
                    });
                }
                this.associations[from] = to;
            },

            /** Charge le fichier ICS depuis l'interface **/
            loadIcsFile(e){
                var fr = new FileReader();
                fr.onloadend = (result) => {
                    this.parseFileContent(fr.result);
                };
                fr.readAsText(e.target.files[0]);
            },

            /** Parse le contenu ICS **/
            parseFileContent(content){

                var analyser = new ICalAnalyser(
                    new Date(),
                    [{startTime: '9:00', endTime: '12:30'}, {startTime: '14:00', endTime: '17:30'}]
                );
                var events = analyser.parse(ICAL.parse(content));
                var after = this.periodStart ? this.moment(this.periodStart) : null;
                var before = this.periodEnd ? this.moment(this.periodEnd) : null;
                var icsName  =  "";
                this.importedEvents = [];
                this.labels = [];

                // On précalcule les correspondances possibles entre les créneaux trouvés
                // et les informations disponibles sur les Workpackage
                console.log(this.store.wps);


                events.forEach(item => {
                    icsName = item.icsfilename;
                    item.mmStart = this.moment(item.start);
                    item.mmEnd = this.moment(item.end);
                    item.imported = false;
                    item.useLabel = "";
                    if( (after == null || (item.mmStart > after)) && (before == null || (item.mmEnd < before ))) {
                        this.importedEvents.push(item);
                        if (this.labels.indexOf(item.label) < 0)
                            this.labels.push(item.label);
                    } else {
                        console.log('Le créneau est hors limite');
                    }
                });

                // En minuscule pour les test de proximité
                let icsNameLC = icsName.toLowerCase();

                var associationParser  = (label) => {
                    if( !label ) return "";
                    let out = "";
                    label = label.toLowerCase();

                    Object.keys(this.store.wps).map((objectKey, index) => {
                        let wpDatas = this.store.wps[objectKey],
                            wpCode = wpDatas.code.toLowerCase(),
                            acronym = wpDatas.acronym.toLowerCase(),
                            code = wpDatas.activity_code.toLowerCase()
                        ;
                        if( (icsNameLC.indexOf(acronym) >= 0 || icsNameLC.indexOf(code) >= 0) || (label.indexOf(acronym) >= 0 || label.indexOf(code) >= 0)){
                            if( label.indexOf(wpCode) >= 0 ){
                                out = objectKey;
                            } else {
                                console.log("Pas de code WP");
                            }
                        } else {
                            // ou dans le label ...
                            console.log(icsNameLC, acronym, code, label);
                            console.log((icsNameLC.indexOf(acronym) >= 0 || icsNameLC.indexOf(code) >= 0));
                            console.log((label.indexOf(acronym) >= 0 || label.indexOf(code) >= 0));
                            console.log("Pas de code/acronyme");
                        }
                    });
                    return out;
                };

                // 'acronym'       => $wpd->getActivity()->getAcronym(),
                //     'activity'      => $wpd->getActivity()->__toString(),
                //     'activity_code' => $wpd->getActivity()->getOscarNum(),
                //     'idactivity'    => $wpd->getActivity()->getId(),
                //     'code' => $wpd->getCode(),


                var associations = {};
                for( var i=0; i<this.labels.length; i++ ){
                    var label = this.labels[i];
                    var corre = associationParser(label);
                    console.log(corre);
                    associations[label] = corre ? corre : "";
                    if ( corre )
                        this.updateLabel(label, corre);
                    else
                        associations[label] = "ignorer"
                }

                /*
                if( this.store.wps  ){
                    Object.keys(this.store.wps).map((objectKey, index) => {
                        associations[objectKey] = associationParser(this.store.wps[objectKey], this.labels);
                    });
                }
                /****/

                this.associations = associations;


                this.importedEvents = EventDT.sortByStart(this.importedEvents);

                this.etape = 2;
                /****/
            },
            applyImport(){
                var imported = [];
                this.importedEvents.forEach(event => {
                    if (event.imported == true) {
                        imported.push(event)
                    }
                });
                if( imported.length > 0 )
                    this.$emit('import', imported);
            }
        }
    }
</script>