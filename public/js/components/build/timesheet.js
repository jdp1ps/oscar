/**
 * Created by jacksay on 17-02-03.
 */
import Vue from "vue";
import VueResource from "vue-resource";
import moment from "mm";
import ical from "ical";
import InTheBox from "in-the-box";
import Papa from "papa-parse";
import Colorpicker from "colorpicker";
import datepicker from "datepicker";

Vue.use(VueResource);

class EventItem {
    constructor(start, end, summary, description, uid = null) {
        this.uid = uid;
        this.summary = summary;
        this.description = description;
        this.start = start;
        this.end = end;
        this.updateMM();
    }

    updateMM() {
        this.mmStart = moment(this.start);
        this.mmEnd = moment(this.end);
    }

    get durationMinutes() {
        return Math.ceil((this.mmEnd - this.mmStart) / 1000 / 60);
    }

    get durationHours() {
        return this.durationMinutes / 60;
    }

    get dayStart() {
        return this.start.slice(0, 10);
    }

    get dayEnd() {
        return this.end.slice(0, 10);
    }

    get hourStart() {
        return this.mmStart.format('H:mm');
    }

    get hourEnd() {
        return this.mmEnd.format('H:mm');
    }

    get percentStart() {
        return 100 / 1440 * (this.mmStart.hours() * 60 + this.mmStart.minutes());
    }

    get percentEnd() {
        return 100 / 1440 * (this.mmEnd.hours() * 60 + this.mmEnd.minutes());
    }

    get day() {
        return 'Le ' + this.mmStart.format('dddd Do MMMM YYYY');
    }

    get month() {
        return 'Mois de ' + this.mmStart.format('MMMM YYYY');
    }

    get year() {
        return 'En ' + this.mmStart.format('YYYY');
    }

    get week() {
        return 'Semaine ' + this.mmStart.format('W, YYYY');
    }
}

class IcalAnalyser {

    constructor(ending = new Date()) {
        if (ending instanceof String) ending = new Date(ending);

        if (!(ending instanceof Date)) throw 'Bad usage, date or string required.';

        this.ending = typeof ending == 'string' ? new Date(ending) : ending;
        this.daysString = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
        this.summaries = [];
    }

    generateItem(item) {

        // POST traitement
        var mmStart = moment(item.start);
        var mmEnd = moment(item.end);

        // Détection des chevauchements
        // découpe la période en 2 morceaux pour n'avoir que des périodes
        // journalières.
        if (mmStart.date() != mmEnd.date()) {

            var part1 = JSON.parse(JSON.stringify(item)),
                part2 = JSON.parse(JSON.stringify(item)),
                splitEnd = mmStart.endOf('day');

            part1.end = splitEnd.toISOString();

            var beginnextDay = splitEnd.add(1, 'day').startOf('day');
            part2.start = beginnextDay.toISOString();

            // Si le deuxième morceau a une durée nulle, on l'ignore
            if (part2.start == part2.end) {
                return this.generateItem(part1);
            }
            return [].concat(this.generateItem(part1)).concat(this.generateItem(part2));
        }
        return [new EventItem(item.start, item.end, item.summary, item.description, item.uid)];
    }

    /**
     * Traitement des événements récursifs.
     *
     * @param item
     * @param rrule
     * @param exdate
     * @returns {Array}
     */
    repeat(item, rrule, exdate = null) {

        var items = [];
        item.recursive = true;

        if (rrule.freq == 'DAILY' || rrule.freq == 'WEEKLY') {
            var fromDate = new Date(item.start);
            var toDate = new Date(item.end);
            var end = rrule.until ? new Date(rrule.until) : this.ending;
            var interval = rrule.interval || 1;
            var pas = rrule.freq == 'DAILY' ? 1 : 7;
            var count = rrule.count || null;
            var byday = rrule.byday || this.daysString;
            if (byday instanceof String) byday = [byday];

            if (count) {
                for (var i = 0; i < count; i++) {
                    let copy = JSON.parse(JSON.stringify(item));
                    copy.start = moment(fromDate).toISOString();
                    copy.end = moment(toDate).toISOString();
                    copy.recursive = true;
                    items = items.concat(this.generateItem(copy));
                    fromDate.setDate(fromDate.getDate() + interval * pas);
                    toDate.setDate(toDate.getDate() + interval * pas);
                }
            } else {
                while (fromDate < end) {
                    let currentDay = this.daysString[fromDate.getDay()];

                    if (!(byday.indexOf(currentDay) < 0 || exdate.indexOf(fromDate.toISOString()) > -1)) {
                        let copy = JSON.parse(JSON.stringify(item));
                        copy.start = moment(fromDate).format();
                        copy.end = moment(toDate).format();
                        copy.recursive = true;
                        items = items.concat(this.generateItem(copy));
                    }
                    fromDate.setDate(fromDate.getDate() + interval * pas);
                    toDate.setDate(toDate.getDate() + interval * pas);
                }
            }
        } else {
            console.log('RECURENCE NON-TRAITEE', rrule);
        }

        if (items.length == 0) {
            console.log(" !!!!!!!!!!!!!!!! RIEN de CRÉÉ", item, rrule);
            console.log(' TO => ', new Date(rrule.until));
            console.log(' TO => ', this.ending);
            console.log(' TO => ', end);
        } else {
            console.log(' ================ ', items.length, ' créé(s)');
        }

        return items;
    }

    parse(icsData) {

        var out = [],
            exceptions = [];

        icsData[2].forEach(d => {
            var item = { warnings: [] },
                rrule = null,
                exdate = [];

            // Extraction des données brutes
            if (d[0] == 'vevent') {
                d[1].forEach(dd => {
                    if (dd[0] == 'uid') item.uid = dd[3];else if (dd[0] == 'rrule') {
                        rrule = dd[3];
                    } else if (dd[0] == 'exdate') {
                        var m = moment.tz(dd[3], dd[1].tzid);
                        exdate.push(m.tz('Europe/Brussels').toISOString());
                    } else if (dd[0] == 'organizer') {
                        item.email = dd[3];
                    } else if (dd[0] == 'description') {
                        item.description = dd[3];
                    } else if (dd[0] == 'dtstart') {
                        var m = moment.tz(dd[3], dd[1].tzid);
                        item.start = m.tz('Europe/Brussels').toISOString();
                    } else if (dd[0] == 'recurrence-id') {
                        var m = moment.tz(dd[3], dd[1].tzid);
                        item.exception = m.tz('Europe/Brussels').toISOString();
                    } else if (dd[0] == 'dtend') {
                        var m = moment.tz(dd[3], dd[1].tzid);
                        item.end = m.tz('Europe/Brussels').toISOString();
                    } else if (dd[0] == 'last-modified') {
                        item.lastModified = moment(dd[3]).format();
                    } else if (dd[0] == 'summary') {
                        item.summary = dd[3];
                        if (this.summaries.indexOf(item.summary) < 0) {
                            this.summaries.push(item.summary);
                        }
                    }
                });

                if (item.exception) {
                    exceptions = exceptions.concat(this.generateItem(item));
                } else {
                    if (rrule) {
                        out = out.concat(this.repeat(item, rrule, exdate));
                    } else {
                        out = out.concat(this.generateItem(item));
                    }
                }
            }
        });

        exceptions.forEach(ex => {
            for (var i = 0; i < out.length; i++) {
                if (out[i].uid == ex.uid && out[i].start == ex.exception) {
                    out.splice(i, 1, ex);
                }
            }
        });

        return out;
    }
}

var OscarTimePicker = {
    props: {
        value: String,
        // Intitulé
        placeholder: {
            type: String,
            default: "Heure HH:MM"
        },
        minuteStep: {
            type: Number,
            default: 15
        }
    },

    data() {
        return {
            error: ""
        };
    },

    template: `<div class="input-group">
          <span class="input-group-addon"><i class="icon-clock"></i> {{ value }}</span>
          <p class="error alert-danger" v-if="error">
          {{ error }}
          </p>
          <input type="time"
                  class="form-control"
                  :placeholder="placeholder"
                  v-model="value"
                  @change="handlerChange"
          />
      </div>`,

    methods: {
        handlerChange() {
            this.normalizeValue();
            this.$emit('input', this.value);
        },

        handlerKeydown(evt) {
            switch (evt.key) {
                case 'ArrowUp':
                    this.increment();
                    break;
            }
        },

        extractHourAndMinute(str) {
            let hour = 0,
                minute = 0;

            // Format ?:?
            if (this.value.indexOf(':') > 0) {
                let split = this.value.split(':');
                hour = parseInt(split[0]);
                minute = parseInt(split[1]);
            } else {
                var data = this.value.replace(/\D/g, '');
                switch (data.length) {
                    case 1:
                    case 2:
                        hour = data;
                        break;

                    case 3:
                        hour = parseInt(data.substring(0, 2));
                        minute = parseInt(data.substring(2)) * 10;
                        if (hour > 23) {
                            hour = parseInt(data.substring(0, 1));
                            minute = parseInt(data.substring(1));
                        }
                        break;

                    case 4:
                        hour = parseInt(data.substring(0, 2));
                        minute = parseInt(data.substring(2));
                        break;

                    default:
                        this.value = "";
                        this.error = "Invalide entry";
                        return;
                }
            }

            if (isNaN(hour) || isNaN(minute) || hour > 23 || minute > 59) {
                throw "Invalide entry";
            }

            let strHour = hour > 9 ? hour : '0' + hour,
                strMinute = minute > 9 ? minute : '0' + minute;

            return {
                hour: hour,
                minute: minute,
                strHour,
                strMinute,
                output: strHour + ':' + strMinute
            };
        },

        normalizeValue() {
            try {
                this.value = this.extractHourAndMinute(this.value).output;
            } catch (err) {
                this.value = "";
                this.error = err;
            }
        },

        //////////////////////////////////////////////////////////////////////
        increment() {
            var val = this.extractHourAndMinute(this.value);
            val.minute += this.minuteStep;
            if (val.minute >= 60) {
                val.minute = val.minute - 60;
                val.hour++;
            }
            this.value = (val.hour > 9 ? val.hour : '0' + val.hour) + ':' + (val.minute > 9 ? val.minute : '0' + val.minute);
            this.handlerChange();
        }
    }
};

var Timesheet = Vue.extend({
    components: {
        timepicker: OscarTimePicker
    },
    data() {
        return {
            icsFile: null,
            test: '2:00',
            events: [], // Liste des plages horaires chargées
            timepack: "day", // Groupement des données
            tags: [],
            workPackages: [],
            selectedColor: '',
            selectedEvent: null,
            formEvent: null,
            total: 0,
            tagsColors: null,
            tagslabels: [],
            mode: [{ key: "day", label: "Journalier", format: "dddd Do MMMM YYYY" }, { key: "week", label: "Hebdomadaire", format: "Wo en YYYY" }, { key: "month", label: "Mensuel", format: "MMMM YYYYY" }, { key: "year", label: "Annuel", format: "YYYY" }]
        };
    },

    template: `<div>
    <h1>Traitement des déclarations</h1>
    <transition name="popup">
        <div class="form-wrapper" v-if="formEvent">
            <form @submit.prevent="ajouterEvent">

                <div class="form-group">
                    <label>Jour</label>
                    <div class="input-group">
                      <span class="input-group-addon">
                        <i class="icon-calendar"></i>
                        {{ formEvent.day }}
                      </span>

                      <input type="date"
                            class="form-control"
                            data-provide="datepicker"
                            data-date-format="yyyy-mm-dd"
                            placeholder="Jour"
                            v-model="formEvent.day"
                            @change="handleDateChange"
                            @input="handleDateChange"
                            @blur="handleDateChange"
                            />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label>De</label>
                        <timepicker v-model="formEvent.startHour"></timepicker>
                    </div>
                    <div class="col-md-6">
                        <label>À</label>
                        <timepicker v-model="formEvent.endHour"></timepicker>
                    </div>
                </div>

                <div class="form-group">
                    <label>Project ou Lot de travail</label>
                    <div class="input-group">
                      <span class="input-group-addon">
                        <i class="icon-archive"></i>
                      </span>
                      <input type="text"
                            class="form-control"
                            id="inputGroupSuccess3"
                            v-model="formEvent.summary"
                            aria-describedby="inputGroupSuccess3Status" />
                    </div>
                </div>

                <div class="form-group">
                    <label>Commentaire</label>
                    <textarea class="form-control" v-model="formEvent.description"></textarea>
                </div>
                <nav class="oscar-buttons">
                    <button @click.prevent="formEvent = null" class="btn btn-default">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </nav>

            </form>
        </div>
    </transition>

     <p>Ajouter une plage : <button class="btn btn-xs- btn-primary" @click.prevent="nouveau">Ajouter un plage</button></p>


    <p>Selectionnez un fichier ICS à importer</p>

    <input type="file" @change="loadIcsFile" v-model="icsFile">

    <div class="calendar" v-show="events.length">
        <h3>
            <i class="icon-tags"></i>
            Légendes
        </h3>
        <p>Voici la liste des différents intitulés trouvés dans le calendrier : </p>
        <div class="legende">
            <header>Plages :</header>
            <pre>{{ tags }}</pre>
            <span v-for="tag, summary in tags" class="plage-legende"
                :style="{ background: tag }"
                @click="currentColor(summary)">
                {{ summary }}
                <a href="" @click.prevent="removeAll(summary)">Supprimer</a>
            </span>
            <div class="colorselector" v-show="selectedColor">
                <div class="input-group colorpicker-component color-picker">
                    <input type="text" value="selectedColor" class="form-control" @change="updateCurrentColor"/>
                    <span class="input-group-addon"><i></i></span>
                </div>
            </div>
        </div>
        <hr>
        <h3>
            <i class=" icon-calendar-outlilne"></i>
            Contenu du calendrier chargé
        </h3>
        <p>Voici les plages horaires issues du calendrier</p>
        <article class="cal-day" v-for="plages, label in byDays">
            <strong class="label">{{ label }}</strong>
            <div class="plages">
                <div v-for="plage in plages"
                        :style="{ background: getColor(plage.summary),left: plage.percentStart +'%', width: (plage.percentEnd - plage.percentStart) +'%' }"
                        class="plage"
                        @contexmenu="handlerContext($event, plage)">
                    <div class="intitule">
                       <small>{{ plage.hourStart }} - {{ plage.hourEnd }}</small>
                       <span class="recursive" v-if="plage.recursive">R</span>
                       <span class="exception" v-if="plage.exception">E</span>
                    </div>
                    <div class="details">
                        <h3>{{ plage.summary }}</h3>
                        <strong>{{ label }}</strong><br>
                        ID <strong>{{ plage.uid }}</strong><span v-if="plage.exception"> (exception de la série {{ plage.exception }})</span><br>
                        Durée : <strong>{{ plage.durationHours }} heure(s)</strong><br>
                        de : <strong>{{ plage.hourStart }}</strong> à <strong>{{ plage.hourEnd }}</strong><br>
                        <p v-if="plage.description">{{ plage.description }}</p>
                        <a href="#" @click.prevent="remove(plage)">Supprimer ce créneaux</a>
                        <a href="#" @click.prevent="editer(plage)">Editer ce créneaux</a>
                    </div>
                </div>
            </div>
        </article>
    </div>
    <section class="events" v-show="events.length">
        <h3>
            <i class="icon-calculator"></i>
            Analyse du temps déclaré
        </h3>
        Mode
        <select name="" id="" v-model="timepack">
            <option v-for="m in mode" :value="m.key">{{ m.label }}</option>
        </select>

        <table class="table timesheet">
            <thead>
                <tr>
                    <th>Période</th>
                    <th v-for="wp in workPackages">
                        <strong>{{ wp }}</strong>
                    </th>
                    <th>Hors WP</th>
                    <th>créneaux</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tr v-for="row in structuredEvents">
                <th>{{ row.label }}</th>
                <td v-for="wp in workPackages">
                    <span class="hours">{{ row.cols[wp] }}</span>
                </td>

                <td>{{ row.cols.other }}</td>
                <td>{{ row.cols.slots }}</td>
                <td>{{ row.cols.total }}</td>

            </tr>
            <tfoot>
                <tr>
                    <th :colspan="workPackages.length + 2">
                        Total
                    </th>
                    <th>
                        {{ total }}
                    </th>
                </tr>
            </tfoot>
        </table>
        <a class="btn btn-primary" @click="toBase64">Télécharger au format CSV</a>
    </section>
    </div>`,
    components: {
        timepicker: OscarTimePicker
    },

    computed: {

        referenceNewTag(tag) {
            console.log('Reference new tag', tag);
        },

        tags() {
            console.log('Mise à jour des tags');
            var tagsList = [];
            this.events.forEach(event => {
                if (tagsList.indexOf(event.summary) < 0) {
                    tagsList.push(event.summary);
                }
            });
            if (!this.tagsColors || !this.tagsColors.length < tagsList.length) {
                this.tagsColors = InTheBox.Color.generateColor(tagsList.length);
            }

            var tags = {};
            tagsList.forEach((summary, i) => {
                tags[summary] = this.tagsColors[i];
            });
            return tags;
        },

        currentTimeFormat() {
            for (var i = 0; i < this.mode.length; i++) {
                if (this.mode[i] == this.timepack) {
                    return this.mode[i].format;
                }
            }
            return 'error';
        },

        byDays() {
            var byDays = {};
            this.events.forEach(event => {
                var currentDay = event.day;
                if (!byDays[currentDay]) {
                    byDays[currentDay] = [];
                }
                byDays[currentDay].push(event);
            });
            return byDays;
        },

        structuredEvents() {
            var data = [];
            var row = null;

            this.total = 0;

            this.events.forEach(event => {
                var currentRow = event[this.timepack];
                if (row == null || row.label != currentRow) {
                    if (row) {
                        data.push(row);
                    }

                    row = {
                        label: currentRow,
                        cols: {}
                    };

                    this.workPackages.forEach(wp => {
                        row.cols[wp] = 0;
                    });

                    row.cols.other = 0;
                    row.cols.slots = 0;
                    row.cols.total = 0;
                }
                if (row.cols[event.summary] != undefined) {
                    row.cols[event.summary] += event.durationHours;
                } else {
                    row.cols.other += event.durationHours;
                }
                row.cols.total += event.durationHours;
                this.total += event.durationHours;
                row.cols.slots++;
            });

            if (row) {
                data.push(row);
            }
            return data;
        }
    },

    filters: {
        date(value) {
            return moment(value).format('Do MMMM YYYY, H:mm');
        }
    },

    methods: {
        handleDateChange(evt) {
            this.formEvent.day = evt.target.value;
        },

        editer(event) {
            this.selectedEvent = event;
            this.formEvent = {
                day: event.dayStart,
                startHour: event.hourStart,
                endHour: event.hourEnd,
                summary: event.summary,
                description: event.description
            };
        },

        nouveau() {
            this.selectedEvent = null;
            var today = moment().format('YYYY-MM-DD');
            this.formEvent = {
                day: today,
                startHour: '09:00',
                endHour: '12:00',
                summary: 'NEW SUMMARY',
                description: ""
            };
        },

        ajouterEvent() {
            let hstart = this.formEvent.startHour;
            if (hstart.length == 4) {
                hstart = '0' + hstart;
            }

            let hend = this.formEvent.endHour;
            if (hend.length == 4) {
                hend = '0' + hend;
            }

            let tz = moment().tz(moment.tz.guess()).format('Z'),
                start = this.formEvent.day + 'T' + hstart + ':00' + tz,
                end = this.formEvent.day + 'T' + hend + ':00' + tz;

            if (this.selectedEvent) {
                console.log("MAJ");
                this.selectedEvent.start = start;
                this.selectedEvent.end = end;
                this.selectedEvent.summary = this.formEvent.summary;
                this.selectedEvent.updateMM();
                this.eventsSort(this.events);
            } else {
                console.log("AJOUT");
                var event = new EventItem(start, end, this.formEvent.summary, this.formEvent.description);
                this.referenceNewTag(event.summary);
                this.events.push(event);
                this.events = this.eventsSort(this.events);
            }
            this.selectedEvent = null;
            this.formEvent = null;
        },

        eventsSort(events) {
            events.sort((a, b) => {
                if (a.dayStart < b.dayStart) return -1;
                if (a.dayStart > b.dayStart) return 1;
                return 0;
            });
            return events;
        },

        /**
         * Click sur une légende
         *
         * @param summary
         */
        currentColor(summary) {
            this.selectedColor = summary;
            this.colorPicker.colorpicker('setValue', this.tags[summary]);
        },

        /**
         * Retourne la couleur pour l'intitulé.
         *
         * @param summary
         * @returns {*}
         */
        getColor(summary) {
            return this.tags[summary];
        },

        /**
         * Mise à jour de la couleur
         */
        updateCurrentColor() {
            if (this.selectedColor && this.tags[this.selectedColor]) {
                this.$set(this.tags, this.selectedColor, this.colorPicker.colorpicker('getValue'));
            }
        },

        handlerKeypress(e) {
            console.log(e);
        },

        removeAll(summary) {
            if (this.selectedColor == summary) {
                this.selectedColor = null;
            }
            var newEvents = [];
            this.events.forEach(event => {
                if (event.summary != summary) {
                    newEvents.push(event);
                }
            });
            this.events = newEvents;
        },

        toBase64() {
            var data = [];
            var headers = ['année', 'mois', 'jour', 'de', 'à'];
            headers = headers.concat(this.workPackages);
            headers.push('Hors WP');

            data.push(headers);

            this.events.forEach(event => {
                var date = moment(event.start);
                var dateEnd = moment(event.end);
                var row = [date.format('YYYY'), date.format('M'), date.format('D'), date.format('HH:mm'), dateEnd.format('HH:mm')];
                var inWP = false;
                this.workPackages.forEach(wp => {
                    if (event.summary == wp) {
                        row.push(event.durationHours.toString().replace('.', ','));
                        inWP = true;
                    } else {
                        row.push(0);
                    }
                });
                if (inWP == false) {
                    row.push(event.durationHours.toString().replace('.', ','));
                } else {
                    row.push(0);
                }
                data.push(row);
            });

            var str = Papa.unparse({
                data: data,
                quotes: true,
                delimiter: ",",
                newline: "\r\n"
            });
            var content = 'data:application/octet-stream;base64,' + btoa(unescape(encodeURIComponent(str)));
            window.open(content);
        },

        handlerContext($event, plage) {
            console.log(arguments);
        },

        toCSV() {},

        remove(item) {
            console.log('delete', item, this.events.indexOf(item));
            if (this.events.indexOf(item) > -1) this.events.splice(this.events.indexOf(item), 1);
        },

        send() {
            this.http.post(this.url, JSON.parse(JSON.stringify(this.events))).then(res => {
                console.log(res);
            }, err => {
                console.log(err);
                flashMessage(err.body);
            });
        },

        loadIcsFile(e) {
            var fr = new FileReader();
            fr.onloadend = result => {
                this.parseFileContent(ICAL.parse(fr.result));
            };
            fr.readAsText(e.target.files[0]);
        },

        /**
         * Analyse le contenu du fichier pour en extraire un objet structuré.
         */
        parseFileContent(data) {
            var events = [];
            if (data.length < 2) throw "Bad format";
            let d = data[2];

            let warnings = [];

            var parser = new IcalAnalyser();
            events = parser.parse(data);

            events.forEach(event => {
                event.tag = this.tags[event.summary];
            });

            this.events = this.eventsSort(events);
        }
    },

    mounted() {
        console.log("MOUNTED", $(this.$el).find('.color-picker'));
        this.colorPicker = $(this.$el).find('.color-picker').colorpicker();
        this.colorPicker.on('changeColor', this.updateCurrentColor);

        this.tagsColors = InTheBox.Color.generateColor(20);
        console.log(this.tagsColors);
    }
});

export default Timesheet;