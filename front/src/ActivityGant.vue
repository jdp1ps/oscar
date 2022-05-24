<template>
  <div class="activity-gant">
    <h1>GANT</h1>
    min : <code>{{ min }}</code> ~ {{ min_date }}<br>
    max : <code>{{ max }}</code> ~ {{ max_date }}<br>

    <div class="bordered-area">
      <div class="render" style="position: relative; background: rgba(255,255,255,.7)"
           :style="{height: (50 + 80*activities.length) + 'px', width: max - min + 'px'}">
        <header style="background: white" :style="{  }" class="header-year">
          &nbsp;
        </header>

        <div v-for="year in yearheader" :style="{left: year.left+'px'}" class="header-year-div">
          {{ year.label }}
        </div>


        <div v-for="(a, i) in activities" class="activity"
             :style="{'top': 80 + (75*i)+'px', width: dayWidth*a.width+'px', left: a.left+'px'}">
          <div class="activity-label">
            <i class="icon-cube"></i>
            <strong class="acronym">
              {{ a.acronym }}
            </strong>
            <em>
              {{ a.label }}
            </em>
            <small v-if="a.type">({{ a.type }})</small>
          </div>
          <div class="milestone" v-for="m in a.milestones" :style="{left: m.left+'px', bottom: 0}">
            <i class="icon-calendar"></i>
            <strong>
              {{ m.label }}
            </strong>
            <small>
              {{ m.date | datation }}
            </small>
          </div>
        </div>
      </div>

    </div>
    <hr>
    <code>{{ url }}</code>
    <pre>{{ activities }}</pre>
  </div>
</template>
<script>
/**
 node node_modules/.bin/vue-cli-service build --name ActivityGant --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/ActivityGant.vue
 */
const months = [
  '',
  'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jui', 'Aou', 'Sep', 'Oct', 'Nov', 'Déc'
]
export default {
  props: {
    url: {
      required: true
    }
  },

  data() {
    return {
      activities: [],
      dayWidth: 1,
      min: 0,
      max: 0,
      min_date: '2000-01-01',
      max_date: '2022-12-31',
    }
  },

  computed: {
    yearheader() {
      let out = [];
      let start = parseInt(this.min_date.substring(0, 4));
      let end = parseInt(this.max_date.substring(0, 4));
      let left = 0;
      for (let i = start; i < end; i++) {
        if (i != start) {
          let d = (new Date(i + '-01-01')).getTime() / 1000 / 60 / 60 / 24;
          left = d - this.min;
        }
        out.push({label: i, left: left});
      }
      return out;
    }
  },

  filters: {
    datation(dateStr) {
      let year = dateStr.substring(0, 4);
      let month = months[parseInt(dateStr.substring(5, 7))];
      return month + ' ' + year;
    }
  },

  methods: {
    fetch() {
      this.$http.get(this.url).then(ok => {
        this.min = ok.data.activities.min_time;
        this.max = ok.data.activities.max_time;
        this.min_date = ok.data.activities.min_date_str;
        this.max_date = ok.data.activities.max_date_str;

        ok.data.activities.items.forEach(activity => {

          let start = activity.start_time - this.min;
          let end = activity.end_time - this.min;

          activity.left = start;
          activity.width = (end - start);

          activity.milestones.forEach(milestone => {
            milestone.left = milestone.date_time - this.min - start;
          })

          if (!start || !end) {
            console.log("pas de debut")
          }
        });

        this.activities = ok.data.activities.items;
      });
    }
  },

  mounted() {
    this.fetch();
  }
}
</script>