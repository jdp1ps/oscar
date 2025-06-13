<template>
  <h3><i class="icon-history"></i>
    Fréquence des envois</h3>

  <p class="alert alert-info">
    Vous ne recevrez des courriels que si vous avez des notifications non-lues dans Oscar.
    Vous ne pouvez pas vous désinscrire des créneaux marqués avec un <i class="icon-lock"></i>.
  </p>

  <form action="" @submit.prevent="save" v-if="notificationsFixed !== null">
    <div class="alert alert-danger" v-if="errors">{{ errors }}</div>
    <input type="hidden" :value="frequency.join(',')" name="frequency"/>
    <table class="uc-frequency">
      <thead class="heading">
      <tr>
        <th>&nbsp;</th>

        <th class="hours" v-for="day in days" @click="toogleDay(day)">
          {{ day }}
        </th>
      </tr>
      </thead>
      <tbody class="content">
      <tr class="line" v-for="hour in hours">
        <th class="hours-label" @click="toogleHour(hour)">
          {{ hour }}:00
        </th>

        <td class="hours-selector"
            :class="{ 'selected': frequency.indexOf(day+hour) >= 0, 'forced': isForced(day+hour) }"
            v-for="day in days"
            :title="isForced(day+hours)?'Vous ne pouvez pas vous désinscrire de ce créneau': ''"
            v-if="!isForced(days)"
            @click="toogleFrequency(day+hour)">
          <i class="icon-lock" v-if="isForced(day+hour)"></i>
          <i class="icon-ok-circled" v-else></i>
        </td>

      </tr>
      </tbody>
    </table>
    <br>
    <button type="submit" :class="{ 'disabled': loading || !changed, 'btn-primary': changed }"
            class="btn btn-default btn-save" v-if="notificationsOverride">
      <i class="icon-floppy" v-if="!loading"></i>
      <i class="icon-spinner animate-spin" v-if="loading"></i>
      Enregistrer
    </button>
  </form>
  <div v-else>
    Fréquences des envois
  </div>
</template>
<script>
import AxiosOscar from "../utils/AxiosOscar.js";

export default {
  data() {
    return {
      notificationsFixed: null,
      notificationsOverride: false,
      parameters: {},
      loading: false,
      frequency: [],
      frequencySnap: null,
      days: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
      hours: ['4', '6', '8', '10', '12', '14', '16', '18', '20', '22'],
      errors: null,
      changed: false
    }
  },

  computed: {
    frequencySnapCurrent() {
      return JSON.stringify(this.frequency);
    }
  },

  watch: {
    frequency: {
      deep: true,
      handler: function (newVal, oldVal) {
        if (!this.loading) {
          if (this.frequencySnap != this.frequencySnapCurrent) {
            this.changed = true;
          } else {
            this.changed = false;
          }
        }
      }
    }
  },


  methods: {
    isForced(str) {
      return this.notificationsFixed.indexOf(str) >= 0
    },

    hasFrequencyInDay(d) {
      for (var i = 0; i < this.frequency.length; i++) {
        if (this.frequency[i].indexOf(d) === 0)
          return true
      }
      return false;
    },

    hasFrequencyInHour(h) {
      for (var i = 0; i < this.frequency.length; i++) {
        if (this.frequency[i].indexOf(h) === 3)
          return true
      }
      return false;
    },

    removeFrequency(f) {
      var index = this.frequency.indexOf(f);
      if (index >= 0)
        this.frequency.splice(index, 1);
    },

    toogleFrequency(f) {
      if (!this.notificationsOverride) return;
      if (this.isForced(f)) {
        return;
      }
      if (this.frequency.indexOf(f) < 0)
        this.frequency.push(f);
      else
        this.frequency.splice(this.frequency.indexOf(f), 1);
    },

    toogleDay(d) {
      if (!this.notificationsOverride) return;
      // On détermine si des créneaux sont selectionnés ?
      var remove = this.hasFrequencyInDay(d);
      for (var i = 0; i < this.hours.length; i++) {
        if (remove)
          this.removeFrequency(d + this.hours[i]);
        else
          this.frequency.push(d + this.hours[i]);
      }
    },

    toogleHour(h) {
      if (!this.notificationsOverride) return;
      var remove = this.hasFrequencyInHour(h);

      for (var i = 0; i < this.days.length; i++) {
        if (remove)
          this.removeFrequency(this.days[i] + h);
        else
          this.frequency.push(this.days[i] + h);
      }
    },

    save() {
      this.loading = true;

      var datas = new FormData();
      datas.append('action', 'frequency');
      datas.append('frequency', this.frequency.join(','));

      AxiosOscar.post('', datas)
          .then(
              success => {
                this.loading = false;
                this.changed = false;
              },
              fail => {
                this.errors = "Erreur : " + fail.body;
              }
          ).then((foo) => {
        this.loading = false;
        this.changed = false;
      });
    },

    fetch() {
      console.log("#################### fetch");
      this.loading = true;
      AxiosOscar.get('?a=frequency').then(
          ok => {
            console.log(ok.data);
            this.notificationsFixed = ok.data.notificationsFixed;
            this.notificationsOverride = ok.data.notificationsOverride;
            this.frequency = ok.data.frequency;
            this.frequencySnap = JSON.stringify(this.frequency);
            this.changed = false;
            this.loading = false;
          },
          ko => {
            console.log('ERROR', ko)
          }
      )
    }
  },

  mounted() {
    this.fetch();
  }
}
</script>