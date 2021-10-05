<template>
  <div>
    <button class="btn btn-primary" @click="handlerOpenSynthesis()" :class="{'disabled': loading}">
      <i class="icon-spinner animate-spin" v-if="loading"></i>
      <i class="icon-calendar" v-else></i>
      Synthèse des déclarations
    </button>

    <div class="overlay-content">
      <a href="#" class="overlay-closer" @click="handlerCloseSynthesis()">Close</a>
      <div v-if="synthese" class="activity-timesheet-synthesis">
        <nav class="text-center">
          <button v-for="p, person_id in persons"
                  class="btn"
                  :class="filterPersons.indexOf(parseInt(person_id)) < 0 ? 'btn-primary' : 'btn-default'"
                  @click="handlerTooglePerson(person_id)">
            {{ p }}
          </button>
        </nav>

        <section v-for="y in sortedDatas.years" class="year" :class="{'open': y.open }">
          <h3 class="line" @click="handlerOpenYear(y)">
            <strong>
              <i class="icon-angle-right" v-show="!y.open"></i>
              <i class="icon-angle-down" v-show="y.open"></i>
              {{ y.label }}
            </strong>
            <em class="total" v-show="!y.open">{{ y.totalmain | hours }}</em>
          </h3>

          <section v-for="p in y.periods" class="month" v-show="y.open" :class="{'open': y.open }">
            <h4 class="line" @click="handlerOpenPeriod(p)">
              <strong>
                <i class="icon-angle-right" v-show="!p.open"></i>
                <i class="icon-angle-down" v-show="p.open"></i>
                {{ p.label }}
              </strong>
              <em class="total" v-show="!p.open">{{ p.totalmain | hours }}</em>
            </h4>

            <section v-for="pers in p.persons" class="person" v-show="p.open" :class="{'open': p.open }" @click="handlerOpenPerson(pers)">
              <h5 class="line">
                <strong>{{ pers.label }}</strong>
                <em class="total">{{ pers.totalmain | hours }}</em>
              </h5>
              <section class="details-person" v-if="pers.open">
                <article class="line">
                  <strong>
                    <i class="icon-cube"></i> {{ mainContext }}
                  </strong>
                  <em>
                    {{ pers.totalmain | hours }}
                  </em>
                </article>
                <section class="othersprojects" v-if="pers.othersprojects.total > 0">

                  <article v-for="projectDuration, projectName in pers.othersprojects.projects" class="line">
                    <strong>
                      <i class="icon-cube"></i> {{ projectName }}
                    </strong>
                    <em style="font-weight: 800">
                      {{ projectDuration | hours }}
                    </em>
                  </article>
                </section>
                <section class="othersprojects" v-if="pers.others.total > 0">
                  <article v-for="otherDuration, otherName in pers.others.context" class="line">
                    <strong>
                      {{ otherName | contextLabel }}
                    </strong>
                    <em>
                      {{ otherDuration | hours }}
                    </em>
                  </article>
                </section>
                <article class="line totalline">
                  <strong>
                    Total pour cette période :
                  </strong>
                  <em>
                    {{ pers.total | hours }}
                  </em>
                </article>
              </section>
            </section>

            <section class="person" v-show="p.open">
              <h5 class="line totalline">
                <strong>Total pour la période {{ p.label }}</strong>
                <em class="total">{{ p.totalmain | hours }}</em>
              </h5>
            </section>

          </section>

          <section class="person" v-show="y.open">
            <h5 class="line totalline">
              <strong>Total pour {{ y.label }}</strong>
              <em class="total">{{ y.totalmain | hours }}</em>
            </h5>
          </section>
        </section>
      </div>
    </div>

    <div class="overlay" v-if="open">

    </div>

  </div>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
node node_modules/.bin/gulp activityTimesheetSynthesisMenuWatch

Pour compiler :
node node_modules/.bin/gulp activityTimesheetSynthesisMenu

 */

let periodStr = function (year, month) {
  return year + '-' + (month < 10 ? '0' + month : month);
};

let months = ["", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Décembre"];

let periodFullStr = function (year, month) {
  return months[month] + ' ' + year;
};

let contextsLabels = {
  'research' : "Autres recherches",
      'conges' : "Congès",
      'training' : "Formation",
      'sickleave' : "Arrêt maladie",
      'other' : "Autre activité"
};

export default {

  props: {
    url: {require: true}
  },

  data() {
    return {
      configuration: null,
      open: false,
      // Donnèes brutes
      synthese: null,
      structuredDatas: {},

      // Filtres
      filterPersons: [],

      // Donnèes calculées au fetch
      mainContext: null, // Projet référent
      mainContextId: null, // Projet référent
      contexts: null, // Autres contextes
      periods: null, // Liste des périodes
      persons: null, // Liste des déclarants
      years: null, // Années

      //
      openYear: [],
      openPeriod: [],
      openPerson: [],
    }
  },

  filters: {
    hours(str) {
      let t = parseFloat(str);
      let rnd = Math.round(t * 100);
      return (rnd / 100).toFixed(2);
    },
    contextLabel(contextKey){
      if( contextsLabels.hasOwnProperty(contextKey) ){
        return contextsLabels[contextKey];
      }
      return contextKey+"*";
    }
  },

  computed: {
    byDate() {
      let bydate = {};
    },

    sortedDatas() {

      // Structure racine
      let out = {
        "totaux": {
          "all": 0.0,
          "totalmain": 0.0,
          "othersprojects": {
            "projects": {},
            "total": 0.0
          },
          "others": {
            "context": {},
            "total": 0.0
          },
        },
        "years": {},
      };

      // Annèes
      this.years.forEach(year => {
        out.years[year] = {
          "key": year,
          "label": year,
          "totalmain": 0.0,
          "open": this.openYear.indexOf(year) >= 0,
          "periods": {}
        }
      });

      //
      this.periods.forEach(period => {
        let year = period.substring(0, 4);
        let month = period.substring(5, 7);
        out.years[year].periods[period] = {
          "key": period,
          "label": periodFullStr(year, parseInt(month)),
          "totalmain": 0.0,
          "persons": {},
          "open": this.openPeriod.indexOf(period) >= 0,
        }
      });

      this.synthese.synthesis.forEach(d => {

        let period = d.period;
        let year = parseInt(d.period.substring(0, 4));
        let month = parseInt(d.period.substring(5, 7));
        let person_id = d.person_id;
        let person = d.displayname;
        let context = d.context;
        let activity_id = d.activity_id;
        let type = d.type;
        let duration = parseFloat(d.duration);

        if (this.filterPersons.indexOf(person_id) >= 0) {
          return;
        }

        if (!out.years[year].periods[period].persons.hasOwnProperty(person_id)) {
          let indexOpen = year+"-"+period+"-"+person_id;
          out.years[year].periods[period].persons[person_id] = {
            "key": person_id,
            "label": person,
            "indexOpen": indexOpen,
            "open": this.openPerson.indexOf(indexOpen) >= 0,
            "totalmain": 0.0,
            "total": 0.0,
            "othersprojects": {
              "projects": {},
              "total": 0.0
            },
            "others": {
              "context": {},
              "total": 0.0
            },
          }
        }

        // --- Comptabilisé pour le projet principal
        if (activity_id == this.mainContextId) {
          out.totaux.totalmain += duration;
          out.years[year].totalmain += duration;
          out.years[year].periods[period].totalmain += duration;
          out.years[year].periods[period].persons[person_id].totalmain += duration;
        }
        // --- Comptabilisé pour les autres projets
        else if (type == "wp") {
          out.years[year].periods[period].persons[person_id].othersprojects.total += duration;
          //
          if (!out.years[year].periods[period].persons[person_id].othersprojects.projects.hasOwnProperty(context)) {
            out.years[year].periods[period].persons[person_id].othersprojects.projects[context] = 0.0;
          }
          out.years[year].periods[period].persons[person_id].othersprojects.projects[context] += duration;
          let project = context;
          if (!out.totaux.othersprojects.projects.hasOwnProperty(project)) {
            out.totaux.othersprojects.projects[project] = 0.0;
          }
          out.totaux.othersprojects.projects[project] += duration;
          out.totaux.othersprojects.total += duration;
        }
        // --- Autre (Congès, enseignement, etc...)
        else {
          out.years[year].periods[period].persons[person_id].others.total += duration;
          if (!out.years[year].periods[period].persons[person_id].others.context.hasOwnProperty(context)) {
            out.years[year].periods[period].persons[person_id].others.context[context] = 0.0;
          }
          out.years[year].periods[period].persons[person_id].others.context[context] += duration;
        }
        out.years[year].periods[period].persons[person_id].total += duration;
        out.totaux.all += duration;
      });

      return out;
    }
  },

  methods: {
    handlerTooglePerson(id) {
      let personId = parseInt(id);
      let indexId = this.filterPersons.indexOf(personId);
      if (indexId < 0) {
        this.filterPersons.push(personId);
      } else {
        this.filterPersons.splice(indexId, 1);
      }
    },

    handlerOpenPerson(person) {
      console.log(person);
      let indexId = this.openPerson.indexOf(person.indexOpen);
      if (indexId < 0) {
        this.openPerson.push(person.indexOpen);
      } else {
        this.openPerson.splice(indexId, 1);
      }
    },

    handlerOpenYear(year) {
      let i = this.openYear.indexOf(year.key);
      if (i < 0) {
        this.openYear.push(year.key);
      } else {
        this.openYear.splice(i, 1);
      }
    },

    handlerOpenPeriod(period) {
      console.log(period.key, this.openPeriod);
      let i = this.openPeriod.indexOf(period.key);
      if (i < 0) {
        this.openPeriod.push(period.key);
      } else {
        this.openPeriod.splice(i, 1);
      }
    },

    handlerOpenSynthesis() {
      this.open = true;
      this.loadSynthesis();
    },

    handlerCloseSynthesis() {
      this.open = false;
      console.log("CLOSE Synthesis 'update'");
    },

    buildStructuredDatas() {
      // @deprecated
    },

    buildSynthese(datas) {
      // TODO Tester les dates de début/fin
      // ...
      this.mainContext = datas.acronym;
      this.mainContextId = datas.activity_id;

      // Constuction de la liste des périodes
      let fromYear = parseInt(datas.start.substring(0, 4));
      let toYear = parseInt(datas.end.substring(0, 4));
      let fromMonth = parseInt(datas.start.substring(5, 7));
      let toMonth = parseInt(datas.end.substring(5, 7));

      let periods = [];
      let years = [];
      let j = fromMonth;
      for (let i = fromYear; i <= toYear; i++) {
        years.push(i);
        for (; j <= 12 || (j <= toMonth && i == toYear); j++) {
          periods.push(periodStr(i, j));
        }
        j = 1;
      }

      let persons = {};
      let contexts = [];

      datas.synthesis.forEach(e => {
        if (!persons.hasOwnProperty(e.person_id)) {
          persons[e.person_id] = e.displayname;
        }
        if (contexts.indexOf(e.context) < 0 && e.activity_id != this.mainContextId) {
          contexts.push(e.context)
        }
      });

      this.periods = periods;
      this.contexts = contexts;
      this.persons = persons;
      this.years = years;

    },

    loadSynthesis() {
      this.loading = "Chargement de la synthèse...";
      this.$http.get(this.url).then(
          ok => {
            console.log(ok);
            this.synthese = ok.body;
            this.buildSynthese(ok.body);
          }, ko => {

          }
      ).then(foo => {
        this.loading = false;
      })
    }
  },

  mounted() {
    console.log("ActivityTimesheetSynthesisMenu.vue");
    this.loadSynthesis();
  }

}
</script>