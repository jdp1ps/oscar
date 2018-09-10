<template>
    <section class="oscar-ui import-ical">
        <h1>Imporation de calendrier</h1>

        <input type="file" @change="handlerFileSelected">



        <div class="row">
            <div class="col-md-8">
                <h2>Créneaux trouvés</h2>
                <section v-for="p in byPeriod">
                    <h2>
                        <i class="icon-calendar"></i> {{ p.label }}
                        <a href="#" class="btn btn-xs btn-danger" @click.prevent="handlerRemovePeriod(p.code)"><i class="icon-trash"></i>Retirer</a>
                    </h2>
                    <article v-for="d in p.days" class="day">
                        <strong class="dayLabel">{{ d.label }}</strong>
                        <span v-for="t in d.timesheets">
                            {{ t.label }} ({{ t | itemDuration }} min)
                        </span>
                        <strong class="dayTotal">{{ d.total | displayMinutes }}</strong>
                    </article>
                </section>
            </div>
            <div class="col-md-4">
                <h2>Intitulés et correspondance</h2>
                <input type="text" class="form-input form-control" placeholder="Filter les intitulés..." v-model="labelFilter">
                <div v-for="label in labels">
                    <i class="icon-tag"></i> {{ label }}
                    <a href="#" class="btn btn-xs btn-danger" @click.prevent="handlerRemoveLabel(label)"><i class="icon-trash"></i> Retirer</a>
                </div>
            </div>
        </div>


        <!--
        <section class="timesheets">
            <article class="card card-xs xs" v-for="timesheet in timesheets">
                <h3 class="card-title">{{ timesheet.summary }} - {{ timesheet.dateStart | formatDay }}</h3>
                <p class="small">{{ timesheet.description }}</p>
            </article>
        </section>
        -->
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  ImportIcalUI --filename.css ImportIcalUI.css --filename.js ImportIcalUI.js --dist public/js/oscar/dist public/js/oscar/src/ImportIcalUI.vue
    export default {
        data(){
            return {
                selectedFile: null,
                daysString: ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'],
                timesheets: [],
                labelFilter: ""
            }
        },

        props: {
            ICAL: { required: true },
            moment: { required: true }
        },

        computed: {
            labels(){
                let labels = [];
                this.timesheets.forEach( item => {
                    if( this.labelFilter != "" && item.label.toLowerCase().indexOf(this.labelFilter.toLowerCase()) < 0 ) return;
                    if (labels.indexOf(item.label) < 0) {
                        labels.push(item.label);
                    }
                });

                labels.sort();
                return labels;
            },

            /** Retourne les créneaux organisés par périodes MOIS-ANNEE > JOUR */
            byPeriod(){
                let out = {};

                this.timesheets.forEach( item => {

                    let m = this.moment(item.start);
                    let e = this.moment(item.end);
                    let total = Math.floor((e.unix() - m.unix())/60);
                    let period = m.format('YYYY-MM');
                    let periodLabel = m.format('MMMM YYYY');
                    let day = m.format('YYYY-MM-DD');
                    let dayLabel = m.format('dddd DD');

                    if( !out.hasOwnProperty(period) ) {
                        out[period] = {
                            period: period,
                            code: period,
                            label: periodLabel,
                            days: {},
                            total: 0.0
                        };
                    }

                    if( !out[period].days.hasOwnProperty(day) ) {
                        out[period].days[day] = {
                            day: day,
                            code: day,
                            label: dayLabel,
                            timesheets: [],
                            total: 0.0
                        };
                    }

                    out[period].days[day].timesheets.push(item);
                    out[period].total += total;
                    out[period].days[day].total += total;
                });

                let output = {};
                // Sort
                Object.keys(out)
                    .sort()
                    .forEach(function(v, i) {
                        output[v] = out[v];
                        let sortedDays = {};
                        Object.keys(output[v].days)
                            .sort()
                            .forEach( dv  => {
                                sortedDays[dv] = output[v].days[dv];
                            });
                        out[v].days = sortedDays;
                    });
                return output;
            }
        },



        methods: {

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
                    console.log(result);
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
                            var itemStart = moment(item.start);
                            if (this.dailyStrategy) {
                                this.dailyStrategy.forEach((copy) => {
                                    var startHourStr = copy.startTime.split(':');
                                    var startHour = parseInt(startHourStr[0]);
                                    var startMinute = parseInt(startHourStr[1]);
                                    var endHourStr = copy.endTime.split(':');
                                    var endHour = parseInt(endHourStr[0]);
                                    var endMinute = parseInt(endHourStr[1]);
                                    var event = {
                                        uid: item.uid,
                                        icsuid: item.icsuid,
                                        icsfileuid: item.icsfileuid,
                                        icsfilename: item.icsfilename,
                                        icsfiledateadded: item.icsfiledateadded,
                                        daily: "allday",
                                        label: item.label,
                                        summary: item.label,
                                        description: item.description,
                                        email: item.email,
                                        start: itemStart.hours(startHour).minutes(startMinute).format(),
                                        end: itemStart.hours(endHour).minutes(endMinute).format()
                                    };
                                    if (rrule) {
                                        out = out.concat(this.repeat(event, rrule, exdate));
                                    } else {
                                        out = out.concat(this.generateItem(event));
                                    }
                                })
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

                this.timesheets = out;
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
                return [{
                    uid: item.uid,
                    icsuid: item.icsuid,
                    icsfileuid: item.icsfileuid,
                    icsfilename: item.icsfilename,
                    icsfiledateaddedd: item.icsfiledateaddedd,
                    label: item.summary,
                    summary: item.summary,
                    lastimport: true,
                    start: item.start,
                    end: item.end,
                    periodCode: mmStart.format('YYYY-MM'),
                    dayCode: mmStart.format('YYYY-MM-DD'),
                    exception: item.exception ? item.exception : null,
                    description: item.description == undefined ? null : item.description
                }];
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
                    } else {
                        console.log(' ================ ', items.length, ' créé(s)')
                    }
                    return items;
                },



            /////////////////////////////////////////////////////////////////////////////////////////////////// HISTORIC
            /** Charge le fichier ICS depuis l'interface **/
            loadIcsFile(e) {
                var fr = new FileReader();
                fr.onloadend = (result) => {
                    this.parseFileContent(fr.result);
                };
                fr.readAsText(e.target.files[0]);
            },

            /** Parse le contenu ICS **/
            parseFileContent(content) {

                var analyser = new ICalAnalyser(
                    new Date(),
                    [{startTime: '9:00', endTime: '12:30'}, {startTime: '14:00', endTime: '17:30'}]
                );
                var events = analyser.parse(ICAL.parse(content));
                var after = this.periodStart ? moment(this.periodStart) : null;
                var before = this.periodEnd ? moment(this.periodEnd) : null;
                var icsName = "";
                this.importedEvents = [];
                this.labels = [];

                // On précalcule les correspondances possibles entre les créneaux trouvés
                // et les informations disponibles sur les Workpackage
                console.log(store.wps);


                events.forEach(item => {
                    icsName = item.icsfilename;
                    item.mmStart = moment(item.start);
                    item.mmEnd = moment(item.end);
                    item.imported = false;
                    item.useLabel = "";
                    if ((after == null || (item.mmStart > after)) && (before == null || (item.mmEnd < before))) {
                        this.importedEvents.push(item);
                        if (this.labels.indexOf(item.label) < 0)
                            this.labels.push(item.label);
                    } else {
                        console.log('Le créneau est hors limite');
                    }
                });

                // En minuscule pour les test de proximité
                let icsNameLC = icsName.toLowerCase();

                var associationParser = (label) => {
                    if (!label) return "";
                    let out = "";
                    label = label.toLowerCase();

                    Object.keys(store.wps).map((objectKey, index) => {
                        let wpDatas = store.wps[objectKey],
                            wpCode = wpDatas.code.toLowerCase(),
                            acronym = wpDatas.acronym.toLowerCase(),
                            code = wpDatas.activity_code.toLowerCase()
                        ;
                        if ((icsNameLC.indexOf(acronym) >= 0 || icsNameLC.indexOf(code) >= 0) || (label.indexOf(acronym) >= 0 || label.indexOf(code) >= 0)) {
                            if (label.indexOf(wpCode) >= 0) {
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
                for (var i = 0; i < this.labels.length; i++) {
                    var label = this.labels[i];
                    var corre = associationParser(label);
                    console.log(corre);
                    associations[label] = corre ? corre : "";
                    if (corre)
                        this.updateLabel(label, corre);
                    else
                        associations[label] = "ignorer"
                }

                /*
                if( store.wps  ){
                    Object.keys(store.wps).map((objectKey, index) => {
                        associations[objectKey] = associationParser(store.wps[objectKey], this.labels);
                    });
                }
                /****/

                this.associations = associations;


                this.importedEvents = EventDT.sortByStart(this.importedEvents);

                this.etape = 2;
                /****/
            },
            applyImport() {
                var imported = [];
                this.importedEvents.forEach(event => {
                    if (event.imported == true) {
                        imported.push(event)
                    }
                });
                if (imported.length > 0)
                    this.$emit('import', imported);
            }
        }
    }
</script>