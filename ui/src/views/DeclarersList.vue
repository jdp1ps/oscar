<template>
  <section>
    <transition name="fade">
      <div class="overlay" v-if="infosPerson">
        <div class="overlay-content" style="flex-basis: 90%; max-height: 90%">
          <h3>
            Détails pour
            <strong>{{ infosPerson.person }}</strong><br>
            pour <strong>{{ $filters.period(infosPerson.period) }}</strong>

            <a href="#" @click.prevent="infosPerson = null"><i class="icon-cancel-outline"></i></a>
          </h3>

          <table class="table bordered table table-condensed">
            <thead>
            <tr>
              <th> ~</th>
              <th v-for="d, i in infosPerson.days">
                <small style="font-weight: 100">{{ d.label }}</small>
                {{ d.i }}
              </th>
              <th class="total"> Total</th>
            </tr>
            </thead>

            <tbody v-for="activity in organize(infosPerson).activities">
            <tr>
              <th :colspan="infosPerson.dayNbr + 2">
                <h4><i class="icon-cube"></i>{{ activity.acronym }}</h4>
              </th>
            </tr>
            <tr v-for="workpackage in activity.workpackages">
              <th class="row-label"><i class="icon-archive"></i>{{ workpackage.code }}</th>
              <td v-for="d, i in infosPerson.days" class="day" :class="{ 'off': d.locked }">
                <strong v-if="workpackage.days[i]">
                  {{ $filters.formatDuration(workpackage.days[i]) }}
                </strong>
                <small v-else>0.0</small>
              </td>
              <th class="total">{{ $filters.formatDuration(workpackage.total) }}</th>
            </tr>
            <tr class="row-total">
              <th>Total</th>
              <td :colspan="infosPerson.dayNbr">&nbsp;</td>
              <th class="total">{{ $filters.formatDuration(activity.total) }}</th>
            </tr>
            </tbody>
            <tbody>
            <tr>
              <th :colspan="infosPerson.dayNbr + 2"><h4><i class="icon-tags"></i>Hors-lot</h4></th>
            </tr>
            <tr v-for="other in organize(infosPerson).others">
              <th class="row-label">{{ other.label }}</th>
              <td v-for="d, i in infosPerson.days" class="day" :class="{ 'off': d.locked }">
                <strong v-if="other.days[i]">
                  {{ $filters.formatDuration(other.days[i]) }}
                </strong>
                <small v-else>0.0</small>
              </td>
              <th class="total">{{ $filters.formatDuration(other.total) }}</th>
            </tr>
            </tbody>

            <tfoot>
            <tr class="row-total">
              <th class="row-label">Total</th>
              <th v-for="d, i in infosPerson.days" class="day" :class="{'off': d.locked }">
                <small>{{ $filters.formatDuration(d.total) }}</small>
              </th>
              <th>{{ $filters.formatDuration(infosPerson.total) }}</th>
            </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="overlay" v-if="error">
        <div class="alert alert-danger">
          <h3>Erreur
            <a href="#" @click.prevent="error =null" class="float-right">
              <i class="icon-cancel-outline"></i>
            </a>
          </h3>
          <p>{{ error }}</p>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="overlay" v-if="loading">
        <div class="overlay-content">
          <p>
            <i class="icon-spinner animate-spin"></i>
            Chargement de la période</p>
        </div>
      </div>
    </transition>

    <nav class="navbar navbar-default">
      <div class="button-group" v-if="period">
        <button class="btn btn-default" @click="previousPeriod()"> &lt;</button>
        <span>Période : </span>
        <select name="mois" v-model="period.month" @change="changePeriod()">
          <option :value="m" v-for="mm,m in months">{{ mm }}</option>
        </select>
        <select name="mois" v-model="period.year" @change="changePeriod()">
          <option :value="y" v-for="y in years">{{ y }}</option>
        </select>
        <strong>{{ period.periodLabel }}</strong>
        <button class="btn btn-default" @click="nextPeriod()"> ></button>
      </div>
    </nav>

    <div class="list" v-if="declarers">
      <article class="card" v-for="d in declarers" style="display: flex">

                    <span style="width: 48px; flex: 0">
                        <img class="thumb32" :src="'//www.gravatar.com/avatar/'+d.mailMd5+'?s=48'" alt="">
                    </span>
        <span style="flex: 1; padding-left: .25em">
                        <strong class="lastname">{{ d.lastName }}</strong>
                        <em class="firstname">{{ d.firstName }}</em>
                        <a href="#" class="link" @click.prevent="details(d)"><i class="icon-calendar"></i>Détails</a>

                        <br>
                        <small>{{ d.affectation }}</small>
                    </span>
        <span class="tags" style="flex: 1">
                        <span class="tag cartouche xs" v-for="p in d.projects">
                            <i class="icon-cubes"></i>{{ p }}</span>
                    </span>
        <span style="flex: 1">
                        <strong class="cartouche secondary1">
                            <i class="icon-calendar"></i>
                            <small>Saisie : </small><strong>{{ $filters.percent(100 / d.details.waitingTotal * d.details.total) }} %</strong>
                            <span class="addon">
                                {{ $filters.round1(d.details.total) }} / {{ $filters.round1(d.details.waitingTotal) }}
                            </span>
                        </strong><br>
                        <strong class="cartouche xs secondary1" :class="d.details.state">
                            <i class="icon-paper-plane"></i>
                            <small>État : <strong>{{ d.details.stateText }}</strong></small>
                        </strong>
                    </span>

        <nav class="actions" style="width: 175px;">
          <!--
          <button class="btn btn-primary btn-xs" v-if="d.details.state == 'PERIOD_NODECLARATION'"
                  @click="recall(d.id, period)">
            <i class="icon-mail">Relancer le déclarant</i>
          </button>
          <br>
          -->
          <a class="btn btn-default btn-xs" v-if="d.url_person" :href="d.url_person">
            <i class="icon-user">Fiche personne</i>
          </a>
        </nav>

      </article>
    </div>
  </section>
</template>
<script>

import axios from "axios";

export default {
  props: {
    urlRecallDeclarer: {required: true}
  },

  data() {
    return {
      declarers: null,
      period: null,
      error: null,
      loading: false,
      infosPerson: null,
      months: {
        1: "Janvier",
        2: "Février",
        3: "Mars",
        4: "Avril",
        5: "Mai",
        6: "Juin",
        7: "Juillet",
        8: "Aout",
        9: "Septembre",
        10: "Octobre",
        11: "Novembre",
        12: "Décembre"
      }
    }
  },

  computed: {
    years() {
      let out = [];
      for (let i = this.period.year - 5; i <= this.period.year + 5; i++) {
        out.push(i);
      }
      return out;
    }
  },

  methods: {

    DurationFilter() {
      return DurationFilter;
    },

    recall(personId, period) {
      console.log(personId, period.periodCode, this.urlRecallDeclarer);
    },

    details(declarer) {
      axios.get(declarer.url_details).then(
          ok => {
            this.infosPerson = ok.data;
          }
      );
    },

    organize(personDatas) {
      let datas = {
        activities: {},
        others: {}
      };

      Object.keys(personDatas.activities).forEach(activityId => {
        var activity = personDatas.activities[activityId];
        datas.activities[activityId] = {
          acronym: activity.acronym,
          id: activity.id,
          label: activity.label,
          total: activity.total,
          workpackages: {},
          days: {}
        };
      });

      Object.keys(personDatas.workpackages).forEach(wpId => {
        var wp = personDatas.workpackages[wpId];
        datas.activities[wp.activity_id].workpackages[wpId] = {
          code: wp.code,
          id: wp.id,
          label: wp.label,
          total: wp.total,
          days: {}
        };
      });

      Object.keys(personDatas.otherWP).forEach(otherKey => {
        var other = personDatas.otherWP[otherKey];
        datas.others[otherKey] = {
          label: other.label,
          code: other.code,
          total: other.total,
          days: {}
        };
      });

      Object.keys(personDatas.days).forEach(date => {
        var day = personDatas.days[date];

        if (day.declarations) {
          day.declarations.forEach(declaration => {
            var wpId = declaration.wp_id;
            var activityId = declaration.activity_id;
            var duration = declaration.duration;

            if (!datas.activities[activityId].days.hasOwnProperty(date)) {
              datas.activities[activityId].days[date] = 0.0;
            }
            datas.activities[activityId].days[date] += duration;

            if (!datas.activities[activityId].workpackages[wpId].days.hasOwnProperty(date)) {
              datas.activities[activityId].workpackages[wpId].days[date] = 0.0;
            }
            datas.activities[activityId].workpackages[wpId].days[date] += duration;
          });
        }
        if (day.othersWP) {
          day.othersWP.forEach(otherInfos => {
            var otherKey = otherInfos.code;
            var duration = otherInfos.duration;
            if (!datas.others[otherKey].days.hasOwnProperty(date)) {
              datas.others[otherKey].days[date] = 0.0;
            }
            datas.others[otherKey].days[date] += duration;
          })

        }
      });
      return datas;
    },

    fetch(p = null) {
      this.loading = "Chargement de la période";
      let url = this.urlRecallDeclarer + "?f=json" + (p ? ('&period=' + p) : '');
      axios.get(url).then(
          ok => {
            if (!(ok.data && ok.data.declarers)) {
              this.error = 'Soucis lors du chargement des données';
            } else {
              this.declarers = ok.data.declarers.sort((a, b) => a.lastName.localeCompare(b.lastName));
              this.period = ok.data.period;
            }
          },
          ko => {
            this.error = ko.body;
          }
      ).then(foo => {
        this.loading = false;
      });
    },

    nextPeriod() {
      let month, year;
      if (this.period.month == 12) {
        month = 1;
        year = this.period.year + 1;
      } else {
        month = this.period.month + 1;
        year = this.period.year;
      }
      this.fetch(year + '-' + month);
    },

    previousPeriod() {
      let month, year;
      if (this.period.month == 1) {
        month = 12;
        year = this.period.year - 1;
      } else {
        month = this.period.month - 1;
        year = this.period.year;
      }
      this.fetch(year + '-' + month);
    },

    changePeriod() {
      let period = this.period.year + '-' + this.period.month;
      this.fetch(period);
    }
  },

  mounted() {
    this.fetch();
  }
}
</script>
<style lang="css">
h4 {
  margin: 1em 0 0 0;
}

table.table-condensed > tbody > tr > th.row-label {
  padding-left: 1em;
}

table.table-condensed > tbody > tr > th.row-label i {
  color: #555;
}

table.table-condensed > tbody > tr > th,
table.table-condensed > tbody > tr > td {
  padding: 2px 4px;
}

th {
  font-size: .9em;
}

td {
  font-size: .8em;
  font-weight: 100;
}

td strong {
  font-weight: 600;
}

.total {
  font-size: .9em;
  text-align: right;
}

thead th {
  text-align: center;
}

tbody td {
  border-right: solid thin #cbd3da;
  text-align: right;
}

tfoot th {
  border-right: solid thin #cbd3da;
  text-align: right;
}

tfoot th.total:nth-child(odd) {
  background: #ebeff3;
}

tbody td:nth-child(odd) {
  background: #ebeff3;
}

thead th small {
  display: block;
}

.row-total {
  background: #ececec;
  font-size: 1em;
}

.row-label {
  text-align: left;
  padding-left: 2em;
}

.day.off {
  background: #a9afbb;
}
</style>