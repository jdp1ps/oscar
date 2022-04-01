<template>
    <section class="validations-admin">
        <transition name="fade">
            <div class="pending overlay" v-if="loading">
                <div class="overlay-content">
                    <i class="icon-spinner animate-spin"></i>
                    {{ loading }}
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="pending overlay" v-if="error">
                <div class="overlay-content">
                    <i class="icon-attention-1"></i>
                    {{ error }}
                </div>
            </div>
        </transition>


      <section class="synthesis heading">
        <div class="label-line">
          <span @click="state = (state == 'period' ? 'person' : 'period')" style="cursor: pointer">
            <i class="icon-angle-left" :class="{'disabled': state == 'period' }"></i>
            <span v-if="state == 'person'">Par Période</span>
            <span v-if="state == 'period'">Par Période</span>
            <i class="icon-angle-right" :class="{'disabled': state == 'person' }"></i>
          </span>
        </div>

        <div v-for="wp in synthesis.headings.current.workpackages" class="main research">
          <span class="value hours">{{ wp.label }}</span>
        </div>

        <div class="main research total">
          <span class="value hours">Total</span>
        </div>

        <div v-for="prj in synthesis.headings.prjs.prjs" :title="prj.label"  class="research">
          <span class="value hours">{{ prj.label }}</span>
        </div>

        <div v-for="other in synthesis.headings.others" :title="other.label" :class="other.group">
          <span class="value hours">{{ other.label }}</span>
        </div>

        <div class="total">
          <span class="value">
            Total
          </span>
        </div>
      </section>

      <section v-for="entry in facet" class="synthesis">
        <div class="label-line">{{ entry.label }}</div>

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

      <section class="synthesis heading">
        <div class="label-line"> Total </div>

        <div v-for="wp in synthesis.headings.current.workpackages" class="main research">
          <span class="value hours">{{ wp.total | duration }}</span>
        </div>

        <div class="main research total">
          <span class="value hours">{{ synthesis.headings.current.total | duration }}</span>
        </div>

        <div v-for="prj in synthesis.headings.prjs.prjs" :title="prj.label"  class="research">
          <span class="value hours">{{ prj.total | duration }}</span>
        </div>

        <div v-for="other in synthesis.headings.others" :title="other.label" :class="other.group">
          <span class="value hours">{{ other.total | duration }}</span>
        </div>

        <div class="total">
          <span class="value">
            {{ synthesis.headings.total | duration }}
          </span>
        </div>
      </section>

    </section>
</template>
<script>

    // node node_modules/.bin/vue-cli-service build --name TimesheetActivitySynthesis --dest public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/TimesheetActivitySynthesis.vue

    const STATE_PERIOD = "period";
    const STATE_PERSON = "person";

    export default {
        name: 'TimesheetActivitySynthesis',

        props: {
          initialdata: {
            default: null,
            required: true
          }
        },

        components: {

        },

        data() {
            return {
                loading: null,
                state: STATE_PERIOD
            }
        },

        computed: {
          synthesis(){
            return this.initialdata;
          },
          facet(){
            if( this.state == STATE_PERIOD )
              return this.synthesis.by_periods;
            else
              return this.synthesis.by_persons;
          }
        },

        filters: {
          duration(v){
            let h = Math.floor(v);
            let m = v - h;
            return h + "h" +m;
          }
        },

        methods: {

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
          console.log('INITIALDATA', this.initialdata);
            this.fetch(true)
        }
    }
</script>