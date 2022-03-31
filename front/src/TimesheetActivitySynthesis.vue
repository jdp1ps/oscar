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

      <section v-for="entry in synthesis.by_persons">
        <strong>{{ entry.label }}</strong>

        <section class="projet">
          <div v-for="wp in entry.datas.current.workpackages">
            {{ wp }}
          </div>
        </section>

        <strong class="total">
          <span class="value">
            {{ entry.total | duration }}
          </span>
          heure(s)
        </strong>
        <hr>
        {{ entry }}
      </section>


       <pre>{{ synthesis }}</pre>

    </section>
</template>
<script>

    // node node_modules/.bin/vue-cli-service build --name TimesheetActivitySynthesis --dest public/js/oscar/dist --no-clean --formats umd,umd-min --target lib src/TimesheetActivitySynthesis.vue

    import PersonAutoCompleter from "./components/PersonAutoCompleter";
    import PersonSchedule from "./components/PersonSchedule";
    import AjaxResolve from "./components/AjaxResolve";



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
                loading: null
            }
        },

        computed: {
          synthesis(){
            return this.initialdata;
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