<template>
  <button class="btn btn-primary" @click="fetch">
    FETCH
  </button>
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
        <th>Activité</th>
        <th>Total hors-activité</th>
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
            <table class="table-condensed table">
              <tbody>
              <tr v-for="activityId in p.activities_id">
                <th>
                  <i class="icon-cube"></i>
                  <strong :title="datas.activities[activityId].acronym +' : ' +datas.activities[activityId].label">{{
                    datas.activities[activityId].acronym }}</strong>
                </th>
                <td>
                  <em v-if="p.activities_details && p.activities_details[activityId]">
                    {{ p.activities_details[activityId].days.length }} jr(s)
                  </em>
                  <small v-else>
                    Rien
                  </small>
                </td>
                <td>
                  <em v-if="p.activities_details && p.activities_details[activityId]">
                    {{ p.activities_details[activityId].events }} elem(s)
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
              <tr v-if="p.activities_details && Object.keys(p.activities_details).length > 1">
                <th>Total</th>
                <td colspan="3" class="text-right">
                  <strong>
                    {{ $filters.formatDuration(p.total_activities) }}
                  </strong>
                </td>
              </tr>
              </tbody>
            </table>
          </td>

          <td class="soustotal text-right">
            <table class="table-condensed table" v-if="p.horslots_details && Object.keys(p.horslots_details).length">
              <tr v-for="(details, lot) in p.horslots_details">
                <th>{{ datas.horslots[lot].label }}</th>
                <td>{{ $filters.formatDuration(details.total) }}</td>
              </tr>
              <tfoot v-if="p.horslots_details && Object.keys(p.horslots_details).length > 1">
              <tr>
                <th>Total</th>
                <td>{{ $filters.formatDuration(p.total_horslots) }}</td>
              </tr>
              </tfoot>
            </table>
            <small v-else>
              Vide
            </small>
          </td>
          <td class="total text-right">
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

            <a :href="'/feuille-de-temps/excel?action=export2&period=' +p.period +'&personid=' + p.person_id"
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
          <th>-&nbsp;</th>
          <th>
            <table class="table-condensed table">
              <tr v-for="(activitiesDetails,activityId) in yeardatas.total_activities_details">
                <th><i class="icon-cube"></i>{{ datas.activities[activityId].acronym }}</th>
                <td><small>{{ activitiesDetails.days }} jr(s)</small></td>
                <td><small>{{ activitiesDetails.events }} elem(s)</small></td>
                <td class="text-right">{{ $filters.formatDuration(activitiesDetails.total) }}</td>
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
          <th class="text-right">
            <table class="table-condensed table">
              <tr v-for="(horslotsDetails,horslots) in yeardatas.total_horslots_details">
                <th>{{ datas.horslots[horslots].label }}</th>
                <td><small>{{ horslotsDetails.days }} jr(s)</small></td>
                <td><small>{{ horslotsDetails.events }} elem(s)</small></td>
                <td class="text-right">{{ $filters.formatDuration(horslotsDetails.total) }}</td>
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
          <th class="text-right">
            <strong>{{ $filters.formatDuration(yeardatas.total) }}</strong>
            <small>/ {{ $filters.formatDuration(yeardatas.periodDuration) }}</small></th>
          <th>&nbsp;</th>
        </tr>
        </tbody>

      </template>
    </table>

  </div>
</template>
<script>
import axios from 'axios';
import AxiosMessage from "../utils/AxiosMessage.js";

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
            console.log('horslots', JSON.stringify(period.horslots_details));
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

              console.log(infosActivityPeriod)
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
      console.log("fetch", this.url);
      axios.get(this.url).then(response => {
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
  td {
    border: 0;
  }

}

.table td .table, .table th .table {
    background: rgba(255, 255, 255, .7) !important;

}

tr.line-total {
  background: rgba(31, 68, 192, 0.1);
  border-top: 2px solid #777;
  font-size: 1.1em;
}

tr.line-total th {
  font-weight: normal;
}
</style>