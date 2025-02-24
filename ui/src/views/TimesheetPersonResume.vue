<template>
  <div v-if="datas">

    <h1>Vos déclarations</h1>
    <div class="overlay" v-if="debug">
      <div class="overlay-content">
        <button @click="debug = null">close</button>
        <pre>{{ debug }}</pre>
      </div>
    </div>

    <table class="table declarations-resume">
      <thead>
      <tr>
        <th>Période</th>
        <th>Déclarations</th>
        <th>Com</th>
        <th>Activité</th>
        <th>Hors-lots</th>
        <th>Total</th>
        <th>Actions</th>
      </tr>
      </thead>

      <template v-for="(yeardatas, year) in years">
        <tr class="heading-year">
          <th colspan="7">{{ year }}</th>
        </tr>
        <tbody class="yearrow">
        <tr v-for="p in yeardatas.periods" :class="{
                'valid-100' : p.total == p.periodDuration,
                'valid-95': p.total >= p.periodDuration*.95 && p.total < p.periodDuration,
                'valid-105': p.total <= p.periodDuration*1.05 && p.total > p.periodDuration,
                'error-105': p.total > p.periodDuration*1.05,
                'error-95': p.total < p.periodDuration*.95,
                'optional': p.activities_id.length == 0,
                'conflict' : p.validation_state == 'conflict',
                'validating' : p.validation_state && p.validation_state.indexOf('send-') == 0,
                'validated' : p.validation_state == 'valid'
                }">
          <th class="period"><strong @click="debug = p">
            <i class="icon-ellipsis" v-if="p.activities_id.length == 0"
               title="Aucune déclaration requise pour cette période"></i>
            <i class="icon-attention-1" v-if="p.activities_id.length && !p.validations_id.length && p.past"
               title="Il faut déclarer pour cette période"></i>
            <i class="icon-calendar" v-if="p.activities_id.length && p.validations_id.length"
               title="Procédure de déclaration en cours"></i>

            {{ $filters.period(p.period) }}</strong>
          </th>
          <td class="required">
            <em v-if="p.activities_id.length">
                            <span v-if="p.validations_id.length">
                                <i :class="'icon-' +p.validation_state"></i>
                                <small v-if="p.validators.length">
                                    Validateur(s) :
                                    <strong class="cartouche" v-for="v in p.validators">
                                        <i class="icon-user"></i>
                                        {{ v }}</strong>
                                </small>
                            </span>

              <span v-if="p.validation_state == 'valid'" class="btn-group btn-group-sm">
                                <a href="#" class="btn" style="color: #555; pointer-events: none">
                                    <i class="icon-calendar"></i>
                                       Feuille de temps
                                </a>

                                <a :href="'/feuille-de-temps/excel?action=export&period=' +p.period +'&personid=' + p.person_id"
                                   v-if="timesheetexcel" class="btn btn-primary btn-sm">
                                    <i class="icon-file-excel"></i>
                                    Excel
                                </a>
                                <a :href="'/feuille-de-temps/excel?action=export2&period=' +p.period +'&personid=' + p.person_id"
                                   v-if="p.validation_state == 'valid'" class="btn btn-primary  btn-sm">
                                    <i class="icon-file-pdf"></i>
                                    PDF
                                </a>
                            </span>


              <em v-if="p.validations_id.length == 0">
                Pas de déclaration envoyée
              </em>
            </em>
            <em v-else>
              Facultatif
            </em>
          </td>
          <td>
            <p class="commentaires" v-if="p.commentaires && p.commentaires.length">
              <span class="commentaire" v-for="commentaire in p.commentaires">
                {{ commentaire }}
              </span>
            </p>
            <em v-else>
              Aucun
            </em>
          </td>
          <td class="soustotal">
            <table class="table-condensed table table-packed">
              <tbody>
              <tr v-for="activityId in p.activities_id">
                <th>
                  <i class="icon-cube"></i>
                  <span :title="datas.activities[activityId].acronym +' : ' +datas.activities[activityId].label">{{
                    datas.activities[activityId].acronym }}</span>
                </th>
                <td>
                  <em v-if="p.activities_details && p.activities_details[activityId]">
                    {{ p.activities_details[activityId].days.length }} jr
                  </em>
                  <small v-else>
                    Rien
                  </small>
                </td>
                <td>
                  <em v-if="p.activities_details && p.activities_details[activityId]">
                    {{ p.activities_details[activityId].events }} cr
                  </em>
                  <small v-else>
                    Rien
                  </small>
                </td>
                <td class="text-right">
                  <strong>
                    {{ $filters.formatDuration(p.total_activities_details[activityId]) }}
                  </strong>
                </td>
              </tr>
              </tbody>
              <tfoot>
                <tr v-if="p.activities_details && Object.keys(p.activities_details).length > 1">
                  <th>Total</th>
                  <td colspan="3" class="text-right">
                    <strong>
                      {{ $filters.formatDuration(p.total_activities) }}
                    </strong>
                  </td>
                </tr>
              </tfoot>
            </table>
          </td>
          <td class="soustotal text-right">
            <table class="table-condensed table" v-if="p.horslots_details && Object.keys(p.horslots_details).length">
              <tr v-for="(details, lot) in p.horslots_details">
                <th>{{ datas.horslots[lot].label }}</th>
                <td><strong>{{ $filters.formatDuration(details.total) }}</strong></td>
              </tr>
              <tfoot v-if="p.horslots_details && Object.keys(p.horslots_details).length > 1">
              <tr>
                <th>Total</th>
                <td><strong>{{ $filters.formatDuration(p.total_horslots) }}</strong></td>
              </tr>
              </tfoot>
            </table>
            <small v-else>
              Vide
            </small>
          </td>
          <td class="soustotal total text-right"  style="white-space: nowrap">
            <i class="icon-time icon-clock"></i>
            <strong>{{ $filters.formatDuration(p.total) }}</strong> <small>/ {{
            $filters.formatDuration(p.periodDuration) }}</small></td>
          <td class="total text-right">
            <em class="text-danger">{{p.error}}</em>
            <span v-if="datas.owner">
              <a class="xs btn btn-primary btn-xs"
                 :href="'/feuille-de-temps/declarant?month=' +p.month +'&year=' +p.year"
                 v-if="p.validation_state == 'conflict'">
                  <i class="icon-edit"></i>
                  Corriger
              </a>
              <a class="xs btn btn-default btn-xs"
                 :href="'/feuille-de-temps/declarant?month=' +p.month +'&year=' +p.year"
                 v-else-if="p.validations_id.length > 0">
                  <i class="icon-zoom-in-outline"></i>
                  Visualiser
              </a>
              <a class="xs btn btn-primary btn-xs"
                 :href="'/feuille-de-temps/declarant?month=' +p.month +'&year=' +p.year" v-else>
                  <i class="icon-calendar"></i>
                  Déclarer
              </a>
          </span>

            <a :href="'/feuille-de-temps/excel?action=export2&period=' +p.period +'&personid=' + datas.person_id"
               v-if="timesheetpreview && p.validation_state != 'valid'" class="btn btn-default btn-sm">
              <i class="icon-file-pdf"></i>
              Prévisualiser (PDF)
            </a>
          </td>
        </tr>
        <tr class="line-total">
          <th>
            Total <strong>{{ year }}</strong>
          </th>
          <th>-</th>
          <th>-</th>
          <th class="soustotal">
            <table class="table-condensed table">
              <tr v-for="(activitiesDetails,activityId) in yeardatas.total_activities_details">
                <th><i class="icon-cube"></i>{{ datas.activities[activityId].acronym }}</th>
                <td><small>{{ activitiesDetails.days }} jr</small></td>
                <td><small>{{ activitiesDetails.events }} cr</small></td>
                <td class="text-right">
                  <strong>{{ $filters.formatDuration(activitiesDetails.total) }}</strong>
                </td>
              </tr>
              <tfoot>
                <tr>
                  <th>Total</th>
                  <td>-</td>
                  <td>-</td>
                  <td class="text-right">
                    <strong>{{ $filters.formatDuration(yeardatas.total_activities) }}</strong>
                  </td>
                </tr>
              </tfoot>
            </table>
          </th>
          <th class="text-right soustotal">
            <table class="table-condensed table">
              <tr v-for="(horslotsDetails,horslots) in yeardatas.total_horslots_details">
                <th>{{ datas.horslots[horslots].label }}</th>
                <td><small>{{ horslotsDetails.days }} jr</small></td>
                <td><small>{{ horslotsDetails.events }} cr</small></td>
                <td class="text-right">
                  <strong>{{ $filters.formatDuration(horslotsDetails.total) }}</strong>
                </td>
              </tr>
              <tfoot>
              <tr>
                <th>Total</th>
                <td>-</td>
                <td>-</td>
                <td class="text-right">
                  <strong>{{ $filters.formatDuration(yeardatas.total_horslots) }}</strong>
                </td>
              </tr>
              </tfoot>
            </table>
          </th>
          <th class="text-right soustotal">
            <strong>{{ $filters.formatDuration(yeardatas.total) }}</strong>
            <small>/ {{ $filters.formatDuration(yeardatas.periodDuration) }}</small></th>
          <th>&nbsp;</th>
        </tr>
        </tbody>
      </template>
    </table>
  </div>
  <div v-else>
    NO DATA {{ datas }}
  </div>

</template>
<script>
import axios from 'axios';
import AxiosOscar from "../utils/AxiosOscar.js";

export default {
  props: {
    url: {
      required: true,
    },
    timesheetpreview: {
      default: false
    },
    timesheetexcel: {
      default: false
    }
  },

  data() {
    return {
      debug: null,
      datas: null
    }
  },

  computed: {
    years() {
      let out = {};
      Object.keys(this.datas.periods).forEach(periodKey => {
        if (!this.datas.periods[periodKey].futur) {

          let period = this.datas.periods[periodKey];

          let split = periodKey.split('-');
          let year = split[0];
          let month = split[1];
          if (!out.hasOwnProperty(year)) {
            out[year] = {
              periods: {},
              total: 0.0,
              periodDuration: 0.0,
              total_activities: 0.0,
              total_horslots: 0.0,
              total_activities_details: {},
              total_horslots_details: {}
            }
          }

          let outYear = out[year];

          outYear.periods[periodKey] = period;
          outYear.total += period.total;
          outYear.periodDuration += period.periodDuration;
          outYear.total_activities += period.total_activities;
          outYear.total_horslots += period.total_horslots;

          // détails hors-lots
          if( period.horslots_details	){
            Object.keys(period.horslots_details).forEach(horslot => {
              if (!outYear.total_horslots_details.hasOwnProperty(horslot)) {
                outYear.total_horslots_details[horslot] = {
                  total: 0.0,
                  days: 0.0,
                  events: 0.0
                }
              }
              outYear.total_horslots_details[horslot].total += period.horslots_details[horslot].total;
            });
          }

          if (period.activities_details) {
            Object.keys(period.activities_details).forEach(activity => {
              let infosActivityPeriod = period.activities_details[activity];
              if (!outYear.total_activities_details.hasOwnProperty(activity)) {
                outYear.total_activities_details[activity] = {
                  total: 0.0,
                  days: 0.0,
                  events: 0.0
                }
              }

              outYear.total_activities_details[activity].total += period.activities_details[activity].total;
              outYear.total_activities_details[activity].days += period.activities_details[activity].days.length;
              outYear.total_activities_details[activity].events += period.activities_details[activity].events;
            });
          }
        }
      });
      return out;
    },

    periods() {
      let periods = [];
      Object.keys(this.datas.periods).forEach(periodKey => {
        periods.push(this.datas.periods[periodKey]);
      });
      return periods;
    }
  },

  methods: {
    getActivity(activityId) {

    },
    fetch() {
      AxiosOscar.get(this.url, {'pendingMsg': "Chargement des feuilles de temps"}).then(response => {
        this.datas = response.data;
      }, error => {
        //console.log(error);
        // AxiosMessage.manageErrorResponse(error);
      })
    }
  },

  mounted() {
    console.log("mounted", this.url);
    this.fetch();
  }
}
</script>

<style>

.table {
  font-size: .9em;
  td {
    border: 0;
  }
}

.table td .table, .table th .table {
    background: rgba(255, 255, 255, .5) !important;
  border-left: none;
}

.table .table tr:nth-child(even) {
  background-color: rgba(255, 255, 255, .5) !important;
}

.table tfoot {
  background-color: rgba(115, 140, 204, 0.2) !important;
}

.table tr .soustotal {
  display: table-cell;
  vertical-align: bottom !important;
}

.table tbody th {
  white-space: nowrap;
  font-weight: normal;
}

tr.line-total {
  background: rgba(31, 68, 192, 0.1);
  border-top: 1px solid #333333;
  border-left: none;
  font-size: 1.1em;
}

.table .table {
  border-width: thin;
  tr {
    border-left-width: thin;
  }
  th,td {
    border-left: none;
    border-right: solid thin #eee;
    padding: 2px;
  }
}

tr.line-total th {
  font-weight: normal;
}

.commentaires .commentaire {
  display: block;
  font-size: .9em;
}

.declarations-resume {
  .heading-year th {
    font-size: 2em;
    text-align: right;
  }
  thead th {
    text-align: center;
    background: #0b93d5;
  }
  .yearrow {
    background: rgba(255,255,255,.8);
  }

  .icon-time {
    display: none;
  }
  .valid-95 {
    .icon-time {
      display: inline-block; color: #00cc66;
      &:before {
        content: '\e840';
      }
    }
  }

  tbody tr {
    border-left: solid 4px #eee;
  }

  .valid-105 {
    .icon-time {
      display: inline-block; color: #3fd53f;
      &:before {
        content: '\e843';
      }
    }
  }

  .error-95 {
    .icon-time {
      display: inline-block; color: #970000;
      &:before {
        content: '\e840';
      }
    }
  }

  .error-105 {
    .icon-time {
      display: inline-block; color: #9e0505;
      &:before {
        content: '\e843';
      }
    }

  }

  .valid-100 {
    .icon-time {
      display: inline-block; color: #00AA00;
    }
  }

  tr.optionnal {
    border-left-color: #5a5a5a;
  }

  tr.conflict {
    border-left-color: #CC0000;
    background: rgba(#990000, .10);
  }

  tr.validated {
    border-left-color: #00AA00;
    background: rgba(#00AA00, .10);
  }

  tr.validating {
    border-left-color: #0b93d5;
    background: rgba(#0b93d5, .10);
  }
}
</style>