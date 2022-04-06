<template>
    <section class="validations-admin">

      <div v-if="initialdata">
      <nav style="display: flex">
        Période du
        <div style="position: relative">
          <period-selector v-model="period_from"
                           :min="period_activity_start"
                           :max="period_to"
                           @change="handlerChangeRefresh"/>
        </div>

        à
        <div style="position: relative">
          <period-selector v-model="period_to"
                           :min="period_from"
                           :max="period_activity_end"
                           @change="handlerChangeRefresh"/>
        </div>

        <a :href="url + '?format=pdf&from=' +this.period_from +'&to=' +this.period_to +'&facet=' +state" download>
          <i class="doc-doc"></i>
          Exporter
        </a>

        <input type="file" @change="handlerFile" id="debug_file" />

      </nav>

      <section class="synthesis heading">
        <div class="label-line">
          <span @click="state = (state == 'period' ? 'person' : 'period')" style="cursor: pointer">
            <i class="icon-angle-left" :class="{'disabled': state == 'period' }"></i>
            <span v-if="state == 'person'">Par Période</span>
            <span v-if="state == 'period'">Par Période</span>
            <i class="icon-angle-right" :class="{'disabled': state == 'person' }"></i>
          </span>
        </div>

        <div v-for="wp in initialdata.headings.current.workpackages" class="main research">
          <span class="value hours">{{ wp.label }}</span>
        </div>

        <div class="main research total">
          <span class="value hours">Total</span>
        </div>

        <div v-for="prj in initialdata.headings.prjs.prjs" :title="prj.label"  class="research">
          <span class="value hours">{{ prj.label }}</span>
        </div>

        <div v-for="other in initialdata.headings.others" :title="other.label" :class="other.group">
          <span class="value hours">{{ other.label }}</span>
        </div>

        <div class="total">
          <span class="value">
            Total
          </span>
        </div>
      </section>

      <section v-for="entry, key in facet" class="synthesis">
        <div class="label-line">
          {{ entry.label }}
        </div>

        <div v-for="wp in entry.datas.current.workpackages" :title="wp.code +' - ' +wp.label" class="main research">
          <span class="value hours">{{ wp.total | duration }}</span>
        </div>

        <div class="main research total">
          <span class="value hours">{{ entry.datas.current.total | duration }}</span>
        </div>

        <div v-for="prj in entry.datas.prjs" :title="prj.label"  class="research">
          <span class="value hours">{{ prj.total | duration }}</span>
        </div>

        <div v-for="other in entry.datas.others" :title="other.label" :class="other.group">
          <span class="value hours">{{ other.total | duration }}</span>
        </div>

        <div class="total">
          {{ entry.total | duration }}
        </div>

      </section>

      <section class="synthesis heading sum">
        <div class="label-line"> Total </div>

        <div v-for="wp in initialdata.headings.current.workpackages" class="main research">
          <span class="value hours">{{ wp.total | duration }}</span>
        </div>

        <div class="main research total">
          <span class="value hours">{{ initialdata.headings.current.total | duration }}</span>
        </div>

        <div v-for="prj in initialdata.headings.prjs.prjs" :title="prj.label"  class="research">
          <span class="value hours">{{ prj.total | duration }}</span>
        </div>

        <div v-for="other in initialdata.headings.others" :title="other.label" :class="other.group">
          <span class="value hours">{{ other.total | duration }}</span>
        </div>

        <div class="total">
          <span class="value">
            {{ initialdata.headings.total | duration }}
          </span>
        </div>
      </section>
      </div>
    </section>
</template>
<script>

    // node node_modules/.bin/vue-cli-service build --name TimesheetActivitySynthesis --dest ../public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/TimesheetActivitySynthesis.vue

    import PeriodSelector from "./components/PeriodSelector";

    const STATE_PERIOD = "period";
    const STATE_PERSON = "person";

    export default {
        name: 'TimesheetActivitySynthesis',

        props: {
          url: {
            default: null,
            required: true
          }
        },

        components: {
          'period-selector': PeriodSelector
        },

        data() {
            return {
                loading: null,
                state: STATE_PERSON,
                period_from: '',
                period_to: '',
                period_activity_start: '',
                period_activity_end: '',
                initialdata: null
            }
        },

        computed: {
          facet(){
            if( !this.initialdata )
              return null;

            if( this.state == STATE_PERIOD )
              return this.initialdata.by_periods;
            else
              return this.initialdata.by_persons;
          }
        },

        filters: {
          duration(v){
            let h = Math.floor(v);
            let m = Math.round(60 * (v - h));
            return h + "h" +m;
          }
        },

        methods: {
          handlerFile(e){
            var input = this.$el.querySelector('#debug_file');
            var reader = new FileReader();
            reader.readAsText(input.files[0], "UTF-8");
            reader.onload = function(evt) {
              console.log(evt.target.result);
              this.applyData(JSON.parse(evt.target.result));
            }.bind(this);
          },
          handlerExport(){
            // somestuff here
          },
          handlerChangeRefresh(){
            this.handlerRefresh();
          },

          applyData(data){
            this.initialdata = data;
            this.period_from = data.period_from;
            this.period_to = data.period_to;
            this.period_activity_start = data.period_activity_start;
            this.period_activity_end = data.period_activity_end;
          },

          handlerRefresh(){
            console.log("start handlerRefresh()");
            this.$http.get(this.url + '?from=' +this.period_from +"&to=" +this.period_to).then(
              ok => {
                this.applyData(ok.data);
              },
              ko => {
                console.log('onrejected', ko);
              }
            );

            console.log('fin handlerRefresh()');
          },

            fetch(clear = true) {
                // this.loading = "Chargement des données";
                //
                // this.$http.get('').then(
                //     ok => {
                //         for( let item in ok.body.periods ){
                //             ok.body.periods[item].open = false;
                //         }
                //         this.declarations = ok.body.periods;
                //         this.declarers = ok.body.declarants;
                //     },
                //     ko => {
                //         this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                //     }
                // ).then(foo => {
                //     this.loading = false
                // });
            }
        },

        mounted() {
            this.handlerRefresh();
        }
    }
</script>