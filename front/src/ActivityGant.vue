<template>
  <div class="activity-gant">
    <h1>VUE GANTT (beta)</h1>

    <div class="bordered-area">
      <div class="render" style="position: relative; background: rgba(255,255,255,.7)"
           :style="{height: (50 + 80*activities.length)*dayWidth + 'px', width: max - min + 'px'}">

        <div v-for="year in yearheader" :style="{left: (dayWidth * year.left)+'px'}" class="header-year-div">
          {{ year.label }}
          <div class="months">&nbsp;
<!--            <span>Jan</span>
            <span>Fev</span>
            <span>Mar</span>
            <span>Avr</span>
            <span>Mai</span>
            <span>Jui</span>
            <span>Jul</span>
            <span>Aou</span>
            <span>Sep</span>
            <span>Oct</span>
            <span>Nov</span>
            <span>Dec</span>-->
          </div>
        </div>

        <div v-for="(a, i) in activities" class="activity" style=""
             :style="{
                'top': 80 + (75*i)+'px',
                width: a.width ? dayWidth*a.width+'px' : 'auto',
                right: a.right !== false ? a.right : 'auto',
                'border-left-style': a.start == '' ? 'dotted' : 'solid',
                'border-right-style': a.end == '' ? 'dotted' : 'solid',
                left: a.left+'px'
            }"
        >
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
          <div class="date-start-end">
            <small class="date-start" v-if="a.start">
              <i class="icon-angle-left"></i>
              {{ a.start | datation }}
            </small>
            <small class="date-end" v-if="a.end">
              {{ a.end | datation }}
              <i class="icon-angle-right"></i>
            </small>
          </div>

          <div class="milestone" v-for="m in a.milestones" :style="{left: (m.left*dayWidth)+'px', bottom: 0}">
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
      for (let i = start; i <= end; i++) {
        if (i != start) {
          let d = (new Date(i + '-01-01')).getTime() / 1000 / 60 / 60 / 24;
          left = d - this.min;
        }
        out.push({label: i, left: left});
      }
      return out;
    },
    todayLeft(){
      let d = (new Date().getTime() / 1000 / 60 / 60 / 24);
      return d - this.min;
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

          activity.left = activity.start_time ? start : 0;
          activity.width = (end - start);
          activity.right = false;

          if( !activity.end ){
            console.log("PAS DE FIN DEFINI");
            activity.right = 0;
          }
          if( !activity.start ){
            activity.left = 0;
          }


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