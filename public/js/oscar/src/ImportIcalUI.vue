<template>
    <section class="oscar-ui import-ical">
        <h1>Imporation de calendrier pour <strong>{{ person }}</strong></h1>

        <div class="alert alert-danger" v-if="icsUidList && icsUidList.length > 0">
            Vous avez déjà importé des fichiers ICS pour cette période, pensez à vérifier si ce nouvel import ne provoque pas l'apparition de doublons :
            <ul>
                <li v-for="name, id in icsUidList"><strong>{{ name }}</strong> <small>( UID: {{ id }})</small></li>
            </ul>
        </div>

        <div class="overlay" v-if="debug">
            <div class="overlay-content">
                <a href="#" @click="debug = null">CLOSE</a>
                <pre>{{ debug }}</pre>
            </div>
        </div>

        <div class="overlay" v-if="alertImport">
            <div class="overlay-content">
                <h3>
                    <i class="icon-attention-1"></i>
                    Vous avez déjà importer cet ICAL
                </h3>

                <p>Sans autre action de votre part, les données déjà importer depuis cet ICAL seront mise à jours</p>
                <hr>

                <a href="#" @click="alertImport = ''" class="btn btn-primary">
                    <i class="icon-cancel-outline"></i> Fermer</a>
            </div>
        </div>

        <div class="overlay" v-if="editCorrespondance" :class="(tutostep > 0 && tutostep != 4) ? 'blur' : ''">
            <div class="overlay-content">
                <i class="icon-cancel-outline overlay-closer" @click="editCorrespondance = null"></i>

                <h3>
                    <i class="tag"></i> Correspondance pour les créneaux <strong>{{ editCorrespondance }}</strong>
                </h3>
                <p>Selectionnez une correspondance pour ce type de créneau : </p>
                <section class="list">
                    <article v-for="c in correspondances" class="correspondance-choose" :class="{ 'selected': labelsCorrespondance[editCorrespondance.toLowerCase()] == c }" @click="handlerChangeCorrespondance(editCorrespondance, c)">
                        <h3>
                            <i class="icon-cube" v-if="c.wp_code"></i>
                            <i class="icon-tag" v-else></i>
                            <strong>{{ c.label }}</strong>
                            <small>{{ c.description }}</small>
                        </h3>
                    </article>
                    <article class="correspondance-choose" :class="{ 'selected': labelsCorrespondance[editCorrespondance.toLowerCase()] == null }" @click="handlerChangeCorrespondance(editCorrespondance, null)">
                        <em><i class="icon-cancel-circled"></i></em>
                        <h3>
                            <strong>Ignorer ces créneaux</strong>
                            <small>Ce type de créneau sera ignoré</small></h3>
                    </article>
                </section>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4" :class="(tutostep > 0 && tutostep != 1) ? 'blur' : ''">
                <div class="card">
                    <h3>Etape 1 : <strong>Mois à importer</strong></h3>
                    <p class="basline">Choississez le mois à importer (Vous ne pouvez importer que un mois terminé)</p>
                    <div>
                        Période de <periodselector :period="periodStart" :max="periodMax" @change="handlerPeriodChange($event)" />
                    </div>
                </div>
            </div>

            <div class="col-md-8" :class="(tutostep > 0 && tutostep != 2) ? 'blur' : ''">
                <div class="card">
                    <h3>Etape 2 : <strong>Fichier ICS (Format ICAL)</strong></h3>
                    <p class="basline">Selectionnez le fichier ICAL (format ICS) à charger depuis votre ordinateur</p>
                    <input type="file" @change="handlerFileSelected">
                </div>
            </div>
        </div>

        <div v-if="timesheets != null && timesheets.length == 0">
            <div class="alert alert-info">
                Aucun créneau chargé depuis le fichier ICS
            </div>
        </div>

        <div class="row" v-else>
            <div class="col-md-4">
                <div class="card">

                <h3>
                    Étape 3 : <strong>Ajuster les correspondances</strong>
                    <small>
                        <i class="icon-help-circled"></i> Aide
                    </small>

                </h3>

                <div class="alert alert-info">
                    <p>
                        Vous trouverez ici les <strong>intitulés</strong> chargés depuis le calendrier.
                    </p>
                </div>

                <div v-if="timesheets != null && timesheets.length > 0">
                    <div class="input-group">
                        <div class="input-group-addon"><i class="icon-filter"></i></div>
                        <input type="text" class="form-input form-control" placeholder="Filter les intitulés..." v-model="labelFilter" />
                    </div>
                    <hr>
                    <div v-for="label in labels" class="card xs correspondance" :class="{ 'match' : labelsCorrespondance[label.toLowerCase()] }">
                        <div class="in-ical">
                            <i class="icon-tag"></i> <strong>{{ label }}</strong>
                        </div>

                        <span v-if="labelsCorrespondance[label.toLowerCase()] && labelsCorrespondance[label.toLowerCase()] != null" class="cartouche card-info">
                        <i class="icon-link-outline"></i>
                        {{ labelsCorrespondance[label.toLowerCase()].label }}
                    </span>

                        <nav>
                            <a href="#" class="text-danger" @click.prevent="handlerRemoveLabel(label)" title="Retirer les créneaux"><i class="icon-trash"></i></a>
                            <a href="#" class="text-danger" @click.prevent="handlerEditCorrespondance(label)" title="Modifier la correspondance"><i class="icon-edit"></i></a>
                        </nav>
                    </div>
                </div>
                </div>

            </div>
            <div class="col-md-8">
                <div class="card">
                <h3>Étape 4 : <strong>Vérifiez et finaliser</strong></h3>

                <div v-if="timesheets != null && timesheets.length > 0">

                    <p class="alert alert-info">
                        <i class="icon-info-circled"></i>
                        Voici les créneaux trouvès dans le calendrier que vous avez chargé. Une fois les créneaux importés,
                        vous pourrez toujours les modifier ou les supprimer depuis l'interface de déclaration.
                    </p>

                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>Jours</th>
                            <th>Créneaux</th>
                            <th>Heures trouvées</th>
                            <!--<th>Existant</th>-->
                            <!--<th>TOTAL</th>-->
                        </tr>
                        </thead>

                        <tbody v-for="p in byPeriod" class="period">
                        <tr class="month-heading" :class="{ 'deja-envoyee': exists[p.code] && exists[p.code].hasValidation }">
                            <th colspan="6">
                                <i class="icon-calendar"></i>
                                {{ p.label }}
                                <a href="#" class="btn btn-xs btn-danger" @click.prevent="handlerRemovePeriod(p.code)"><i class="icon-trash"></i>Retirer</a>
                                <span class="message-erreur" colspan="4" v-if="exists[p.code] && exists[p.code].hasValidation">
                                    <i class="icon-warning-empty"></i>
                                    <strong>Impossible d'envoyer ces créneaux</strong> Vous avez déjà soumis cette période à validation
                                </span>
                            </th>
                        </tr>

                        <tr v-for="d in p.days">
                            <th>&nbsp;

                            </th>
                            <th class="jour" @click.shift="debug = d">{{ d.label }}</th>
                            <td class="jour-description">
                                <div v-for="t in d.timesheets" class="creneau" :class="{ 'ignored': !t.importable,  'imported': t.imported }">
                                    <i v-if="t.warning" class="icon-attention" :title="t.warning"></i>
                                    <i class="icon-calendar" v-if="t.importable"></i>
                                    <i class="icon-cancel-alt" v-else></i>
                                    <strong>{{ t.label }} ({{ t | itemDuration }} min)</strong>
                                    <span v-if="t.destinationLabel" class="cartouche xs">
                                        <i class="icon-cube" v-if="t.destinationCode == 'wp'"></i>
                                        <i class="icon-link-ext" v-else></i>
                                        {{ t.destinationLabel }}
                                    </span>
                                    <nav>
                                        <i class="icon-trash" @click="handlerRemoveTimesheet(t)" title="Supprimer ce créneau"></i>
                                    </nav>
                                    <small v-if="!t.importable"><i class="icon-cancel-circled"></i>Ce créneau ne peut pas être importé.</small>
                                    <small v-else-if="!t.imported">
                                        Ce créneau sera ignoré :
                                        <span v-if="t.warning">{{ t.warning }}</span>
                                        <span v-else>Pas de correspondance pour ce type de créneau</span>
                                    </small>
                                </div>
                            </td>

                            <td class="jour-heures">
                                <strong>{{ d.totalImport | displayMinutes }}</strong>
                            </td>

                            <!--<td class="jour-heures">-->
                                <!--<em v-if="d.exists > 0.0">{{ d.exists | displayMinutes }}</em>-->
                                <!--<em v-else>~</em>-->
                            <!--</td>-->
                            <!--<td class="jour-heures" :class="{ 'excess': d.total > d.max }">-->
                                <!--<i class="icon-attention" title="Dépassement du temps autorisé" v-if="d.total > d.max"></i>-->
                                <!--{{ d.total | displayMinutes }}-->
                            <!--</td>-->

                        </tr>
                        </tbody>
                    </table>

                </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">&nbsp;</div>
            <div class="col-md-8">
                <form action="" method="post" v-if="sendData.length" style="margin-bottom: 3em">
                    <input type="hidden" v-model="JSON.stringify(sendData)" name="timesheets" />
                    <div class="alert alert-warning" v-if="alreadyImported">
                        <h3>Importation précédente ?</h3>

                        <input type="hidden" name="previousicsuid" :value="importedIcsFileUid" >


                        <label for="action1" class="radio-selection" :class="previousicsuidaction == 'remove' ? 'selected' : ''">
                            <i class="icon icon-cw-outline"></i>
                            <span>Mettre à jour les anciens créneaux (Supprimer ceux qui n'existent plus, ajouter les nouveau et modifier ceux qui ont été modifiés)</span>
                            <input type="radio" name="previousicsuidremove" v-model="previousicsuidaction" value="remove" id="action1" checked>
                        </label>

                        <label for="action2" class="radio-selection" :class="previousicsuidaction == 'keep' ? 'selected' : ''">
                            <i class="icon icon-attention"></i>
                            <span>
                                Concerver les anciens créneaux importés et <strong>ajouter les nouveaux</strong>.<br>
                                <small>Cette action risque d'ajouter des créneaux en double dans votre déclaration, pensez à bien vérifier les informations avant de soumettre votre déclaration</small>
                            </span>

                            <input type="radio" name="previousicsuidremove" v-model="previousicsuidaction" value="keep" id="action2">
                        </label>


                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">Importer les créneaux</button>
                </form>

            </div>
        </div>
    </section>
</template>
<script>
    // nodejs ./node_modules/.bin/poi watch --format umd --moduleName  ImportIcalUI --filename.css ImportIcalUI.css --filename.js ImportIcalUI.js --dist public/js/oscar/dist public/js/oscar/src/ImportIcalUI.vue


    export default {
        data(){
            return {
                previousicsuidaction: 'remove',
                selectedFile: null,
                daysString: ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'],
                timesheets: null,
                labelFilter: "",
                // @todo à récupérer depuis le serveur
                periodEnd: "2018-12",
                debug: null,
                labelsCorrespondance: {},
                editCorrespondance: null,
                tutostep: 0,
                importedIcsFileUid: null,
                alertImport: ""
            }
        },

        components: {
            'periodselector': require('./PeriodSelector.vue').default
        },

        props: {
            icsUidList: { required: true },
            ICAL: { required: true },
            moment: { required: true },
            dayLength: { required: true },
            exists: { default: {} },
            correspondances: { required: true },
            periodStart: { required: true },
            periodMax: { required: true },
            person: { required: true },
            personId: { required: true }
        },

        computed: {

            alreadyImported(){
                let imported = false;
                Object.keys(this.icsUidList).forEach(uid => {
                    if( uid == this.importedIcsFileUid )
                        imported = true;
                });
                return imported;
            },

            sendData(){
                if( !this.timesheets ) return [];
                let datas = [];
                this.timesheets.forEach(item=>{
                    if( item.imported )
                        datas.push(item)
                });
                return datas;
            },
            labels(){
                let labels = [];
                if( this.timesheets ){
                    this.timesheets.forEach( item => {
                        if( this.labelFilter != "" && item.label.toLowerCase().indexOf(this.labelFilter.toLowerCase()) < 0 ) return;
                        if (labels.indexOf(item.label) < 0) {
                            labels.push(item.label);
                        }
                    });
                    labels.sort();
                }

                return labels;
            },

            /** Retourne les créneaux organisés par périodes MOIS-ANNEE > JOUR */
            byPeriod(){
                let out = {}, output = {};

                if( this.timesheets ) {

                    this.timesheets.forEach(item => {

                        let m = this.moment(item.start);
                        let e = this.moment(item.end);
                        let total = Math.floor((e.unix() - m.unix()) / 60);
                        let period = m.format('YYYY-MM');
                        let periodLabel = m.format('MMMM YYYY');
                        let day = m.format('YYYY-MM-DD');
                        let daySimple = m.format('DD');
                        let dayLabel = m.format('dddd DD');

                        if (!out.hasOwnProperty(period)) {
                            out[period] = {
                                validation: false,
                                existsDatas: this.exists[period],
                                exists: 0.0,
                                period: period,
                                code: period,
                                label: periodLabel,
                                days: {},
                                total: 0.0
                            };
                            if( this.exists.hasOwnProperty(period) ){
                                out[period].validation = this.exists[period].hasValidation;
                            }
                        }

                        if (!out[period].days.hasOwnProperty(day)) {

                            let hasDeclaration = this.exists[period] ? this.exists[period].hasValidation : false;

                            out[period].days[day] = {
                                toto: 'tata',
                                exists: 0.0,
                                declaration: hasDeclaration,
                                totalImport: 0.0,
                                total: 0.0,
                                closed: false,
                                closedReason: "",
                                day: day,
                                max: 0.0,
                                code: day,
                                label: dayLabel,
                                timesheets: []
                            };

                            if( this.exists.hasOwnProperty(period) && this.exists[period].days.hasOwnProperty(daySimple) ){
                                out[period].days[day].exists += this.exists[period].days[daySimple].duration * 60;
                                out[period].days[day].total += this.exists[period].days[daySimple].duration * 60;
                                out[period].days[day].closed += this.exists[period].days[daySimple].closed;
                                out[period].days[day].max = this.exists[period].days[daySimple].maxLength * 60;
                                out[period].days[day].closedReason += this.exists[period].days[daySimple].closedReason;
                            }
                        }

                        out[period].days[day].timesheets.push(item);
                        out[period].total += total;
                        out[period].days[day].total += total;
                        out[period].days[day].totalImport += total;
                    });

                    // Sort
                    Object.keys(out)
                        .sort()
                        .forEach(function (v, i) {
                            output[v] = out[v];
                            let sortedDays = {};
                            Object.keys(output[v].days)
                                .sort()
                                .forEach(dv => {
                                    sortedDays[dv] = output[v].days[dv];
                                });
                            out[v].days = sortedDays;
                        });
                }
                return output;
            }
        },

        methods: {
            toggleImported(timesheet){
                let imported = timesheet.imported;
                if (imported == true) {
                    timesheet.imported = false;
                } else {
                    if( timesheet.importable == true ){
                        timesheet.imported = true;
                    }
                }
            },

            handlerPeriodChange( period ){
                document.location = '?period=' + period+"&person=" + this.personId;
            },

            handlerRemoveTimesheet(timesheet){
                this.timesheets.splice(this.timesheets.indexOf(timesheet), 1);
            },

            handlerChangeCorrespondance(editCorrespondance, dest){
                this.timesheets.forEach(t => {
                    if( t.label == editCorrespondance ){
                        if ( dest == null ){
                            t.destinationCode = "";
                            t.destinationId = -1;
                            t.destinationLabel = "";
                            t.imported = false;
                        } else {editCorrespondance
                            if(  t.importable == true ){
                                t.destinationCode = dest.code;
                                t.destinationId = dest.id;
                                t.destinationLabel = dest.label;
                                t.imported = true;
                            }
                        }

                    }
                })
                this.labelsCorrespondance[editCorrespondance.toLowerCase()] = dest;
                if( window.localStorage ){
                    window.localStorage.setItem('labelsCorrespondance', JSON.stringify(this.labelsCorrespondance));
                }
                this.editCorrespondance = null;
            },

            handlerEditCorrespondance( label ){
                this.editCorrespondance = label;
            },

            test(){


            },

            /**
             * Supprime les créneaux de la période.
             */
            handlerRemovePeriod( period ){
                let newTimesheets = [];
                this.timesheets.forEach(timesheet => {
                    if( timesheet.periodCode != period ){
                        newTimesheets.push(timesheet);
                    }
                })
                this.timesheets = newTimesheets;
            },

            handlerRemoveLabel( label ){
                let newTimesheets = [];
                this.timesheets.forEach(timesheet => {
                    if( timesheet.label != label ){
                        newTimesheets.push(timesheet);
                    }
                })
                this.timesheets = newTimesheets;
            },

            handlerFileSelected(e) {
                var fr = new FileReader();
                fr.onloadend = (result) => {
                    this.parseICAL(fr.result);
                };
                fr.readAsText(e.target.files[0]);
            },

            parseICAL( content ) {
                if (!content) return;


                var ICAL = this.ICAL,
                    moment = this.moment,
                    icsData = ICAL.parse(content);

                // local TZ
                var defaultTimeZone = moment.tz.guess()
                    , out = []
                    , exceptions = [];

                // Données concernant le fichier ICS
                var icsfilename = icsData[1][0][3];
                var icsfileuid = icsData[1][1][3];
                var icsfiledateaddedd = moment().format('YYYY-MM-DD');

                this.importedIcsFileUid = icsfileuid;
                if( this.alreadyImported ){
                    this.alertImport = "Vous avez déjà importez ce calendrier, les créneaux de l'importation précédente seront supprimés";
                }

                // Events
                icsData[2].forEach((d) => {

                    var item = {
                            'icsfileuid': icsfileuid,
                            'icsfilename': icsfilename,
                            'icsfiledateaddedd': icsfiledateaddedd,
                            warnings: []
                        },
                        rrule = null,
                        exdate = [];

                    // Extraction des données brutes
                    if (d[0] == 'vevent') {
                        d[1].forEach((dd) => {

                            if (dd[0] == 'uid') {
                                item.uid = dd[3];
                                item.icsuid = dd[3];
                            } else if (dd[0] == 'rrule') {
                                rrule = dd[3];
                            }

                            else if (dd[0] == 'exdate') {
                                var m = moment.tz(dd[3], dd[1].tzid);
                                exdate.push(m.tz(defaultTimeZone).toISOString());
                            }

                            else if (dd[0] == 'organizer') {
                                item.email = dd[3];
                            }

                            else if (dd[0] == 'description') {
                                item.description = dd[3];
                                if (item.description == 'undefined') {
                                    item.description = '';
                                }
                            }

                            else if (dd[0] == 'dtstart') {
                                var m = moment.tz(dd[3], dd[1].tzid);
                                item.start = m.tz(defaultTimeZone).format();
                            }

                            else if (dd[0] == 'recurrence-id') {
                                item.recurenceid = dd[2];
                                var m = moment.tz(dd[3], dd[1].tzid);
                                item.exception = m.tz(defaultTimeZone).format();
                            }

                            else if (dd[0] == 'dtend') {

                                var m = moment.tz(dd[3], dd[1].tzid);
                                item.end = m.tz(defaultTimeZone).format();
                            }

                            else if (dd[0] == 'last-modified') {
                                item.lastModified = moment(dd[3]).format();
                            }

                            else if (dd[0] == 'summary') {
                                item.summary = item.label = dd[3];
                            }
                            else if (dd[0] == 'x-microsoft-cdo-alldayevent') {
                                item.daily = "allday";
                            }
                        });

                        if (item.exception) {
                            exceptions = exceptions.concat(this.generateItem(item));
                        }
                        else if (item.daily == "allday") {

                            // On commence par detecter
                            let start = this.moment(item.start);
                            let end = this.moment(item.end);

                            let jours = end.diff(start, 'days');
                            let generatedEnd = start.add(this.dayLength, 'hours');
                            let generated = null;

                            for( let i = 0; i<jours; i++ ){
                                generatedEnd = this.moment(start).add(this.dayLength*60, 'minutes');
                                generated = JSON.parse(JSON.stringify(item));
                                generated.start = this.moment(start);
                                generated.end = this.moment(generatedEnd);

                                out = out.concat(this.generateItem(generated));
                                start.add(1, 'days');
                            }
                        } else {
                            if (rrule) {
                                out = out.concat(this.repeat(item, rrule, exdate));
                            } else {
                                out = out.concat(this.generateItem(item));
                            }
                        }
                    }
                });

                // Exclusion
                let result = [];

                out.forEach(item => {
                    if( this.periodStart && (item.periodCode < this.periodStart || item.periodCode > this.periodStart) ) return;
                    result.push(item);
                })

                this.timesheets = result;


            },

            generateItem(item) {
                let moment = this.moment,
                    mmStart = moment(item.start),
                    mmEnd = moment(item.end);

                // Détection des chevauchements
                // découpe la période en 2 morceaux pour n'avoir que des périodes
                // journalières.

                if (mmStart.date() != mmEnd.date()) {

                    var part1 = JSON.parse(JSON.stringify(item))
                        , part2 = JSON.parse(JSON.stringify(item))
                        , splitEnd = mmStart.endOf('day');

                    part1.end = splitEnd.toISOString();

                    var beginnextDay = splitEnd.add(1, 'day').startOf('day');
                    part2.start = beginnextDay.toISOString();

                    // Si le deuxième morceau a une durée nulle, on l'ignore
                    if (part2.start == part2.end) {
                        return this.generateItem(part1)
                    }
                    return [].concat(this.generateItem(part1)).concat(this.generateItem(part2));
                }

                let period = mmStart.format('YYYY-MM'),
                    day = mmStart.format('YYYY-MM-DD'),
                    daySimple = mmStart.format('DD'),
                    dayInt = mmStart.format('D'),
                    importable = true,
                    destinationCode = "",
                    destinationId = -1,
                    destinationLabel = "",
                    imported = false,
                    correspondance = this.findCorrespondance(item.label);

                if( this.hasValidationPeriod(period) ){
                    importable = false;
                }

                if( correspondance ){
                    destinationCode = correspondance.code;
                    destinationId = correspondance.wp_id;
                    destinationLabel = correspondance.label;
                    imported = true;
                }

                let warning = "";

                // JOUR FERMÉ
                if( this.exists[period] && this.exists[period].days[dayInt].closed ){
                    warning = this.exists[period].days[dayInt].closedReason;
                    imported = false;
                }



                // Détection du lot


                return [{
                    uid: item.uid,
                    icsuid: item.icsuid,
                    icsfileuid: item.icsfileuid,
                    icsfilename: item.icsfilename,
                    icsfiledateaddedd: item.icsfiledateaddedd,
                    destinationLabel: destinationLabel,
                    destinationCode: destinationCode,
                    destinationId: destinationId,
                    label: item.summary,
                    summary: item.summary,
                    importable: importable,
                    imported: imported,
                    warning: warning,
                    lastimport: true,
                    start: item.start,
                    end: item.end,
                    periodCode: period,
                    dayCode: day,
                    exception: item.exception ? item.exception : null,
                    description: item.description == undefined ? null : item.description
                }];
            },

            findCorrespondance( text ){

                let tofind = text.toLowerCase();

                if( !this.labelsCorrespondance.hasOwnProperty(tofind) ){
                    this.labelsCorrespondance[tofind] = null;

                    let match = null;

                    for( var i=0; i<this.correspondances.length; i++ ){

                        let wps = this.correspondances[i];


                        if( wps.code && (tofind.indexOf(wps.code.toLowerCase()) > -1 || tofind.indexOf(wps.label.toLowerCase()) > -1) ){
                            match = wps;
                            this.labelsCorrespondance[tofind] = match;
                            return this.labelsCorrespondance[tofind];
                        }

                        if( wps.acronym && tofind.indexOf(wps.acronym.toLowerCase()) > -1 ){
                            match = wps;

                            if( tofind.indexOf(wps.wp_code.toLowerCase()) > -1 ){
                                this.labelsCorrespondance[tofind] = match;
                                return this.labelsCorrespondance[tofind];
                            }
                        }
                    }

                    this.labelsCorrespondance[tofind] = match;
                }
                return this.labelsCorrespondance[tofind];
            },

            hasValidationPeriod( period ){
                if( this.exists && this.exists.hasOwnProperty(period) ){
                    return this.exists[period].hasValidation;
                }
                return false;
            },

                /**
                 * Traitement des événements récursifs.
                 *
                 * @param item
                 * @param rrule
                 * @param exdate
                 * @returns {Array}
                 */
                repeat(item, rrule, exdate = null) {


                    var items = [],
                        moment = this.moment;


                    item.recursive = true;

                    if (rrule.freq == 'DAILY' || rrule.freq == 'WEEKLY') {
                        var fromDate = new Date(item.start);
                        var toDate = new Date(item.end);
                        var end = rrule.until ? new Date(rrule.until) : this.ending;
                        var interval = rrule.interval || 1;
                        var pas = rrule.freq == 'DAILY' ? 1 : 7;
                        var count = rrule.count || null;
                        var byday = rrule.byday || this.daysString;

                        if (byday instanceof String)
                            byday = [byday];

                        if (count) {
                            for (var i = 0; i < count; i++) {
                                let copy = JSON.parse(JSON.stringify(item));
                                copy.start = moment(fromDate).toISOString();
                                copy.end = moment(toDate).toISOString();
                                copy.recursive = true;
                                if( exdate.indexOf(fromDate.toISOString()) < 0 ) {
                                    items = items.concat(this.generateItem(copy));
                                }
                                fromDate.setDate(fromDate.getDate() + (interval * pas));
                                toDate.setDate(toDate.getDate() + (interval * pas));
                            }
                        }
                        else {
                            while (fromDate < end) {
                                let currentDay = this.daysString[fromDate.getDay()];
                                if( item.daily == "allday" && exdate.indexOf(moment(fromDate).format("YYYY-MM-DD")+'T00:00:00.000Z') > -1 ){
                                } else if (!(byday.indexOf(currentDay) < 0 || exdate.indexOf(fromDate.toISOString()) > -1 )) {
                                    let copy = JSON.parse(JSON.stringify(item));
                                    copy.start = moment(fromDate).format();
                                    copy.end = moment(toDate).format();
                                    copy.recursive = true;
                                    items = items.concat(this.generateItem(copy));
                                }
                                fromDate.setDate(fromDate.getDate() + (interval * pas));
                                toDate.setDate(toDate.getDate() + (interval * pas));
                            }
                        }
                    } else {
                        console.log('RECURENCE NON-TRAITEE', rrule);
                    }

                    if (items.length == 0) {
                        console.log(" !!!!!!!!!!!!!!!! RIEN de CRÉÉ", item, rrule)
                        console.log(' TO => ', new Date(rrule.until))
                        console.log(' TO => ', this.ending)
                        console.log(' TO => ', end)
                    }
                    return items;
                }
        },
        mounted(){
            if( window.localStorage && window.localStorage.getItem('labelsCorrespondance') ){
                this.labelsCorrespondance = JSON.parse(window.localStorage.getItem('labelsCorrespondance'));
            }
        }
    }
</script>