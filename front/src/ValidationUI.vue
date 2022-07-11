<template>
    <section>
        VALIDATIONS 2
      <div class="row">
        <div class="col-md-4">
          MENU
          <hr>
          <h4>
            <i class="icon-users"></i>
            Déclarants</h4>
          <article v-for="p in categories.persons" class="card xs">
            <strong>{{ p.declarer_fullname }}</strong>
            <small> ({{ p.declarer_affectation }})</small>
            {{ p }}
          </article>

        </div>
        <div class="col-md-8">
          Affichage
          <hr>
          {{ stackedDatas }}
        </div>
      </div>
      <pre v-if="synthesis">{{ synthesis }}</pre>
    </section>
</template>
<script>
    /******************************************************************************************************************/
    /* ! DEVELOPPEUR
    Depuis la racine OSCAR :

    cd front

    node node_modules/.bin/vue-cli-service build --name ValidationUI --dest ../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib src/ValidationUI.vue

     */

    export default {

        props: {
            url: { required: true }
        },

        data(){
            return {
              synthesis: null,
              filter: null,
              group: 'person'
            }
        },

        computed:{
            stackedDatas(){
              let ouput = {};
              return ouput;
            },

            categories(){
              let out = {
                'persons' : {},
                'projects': {},
                'periodes': {}
              };
              if( this.synthesis ){
                this.synthesis.forEach(e => {
                  let declarer_id = e.declarer_id;
                  let activity_id = e.activity_id;
                  let spt = e.period.split('-');
                  let year = spt[0];
                  let month = spt[1];
                  let validable = e.validable;

                  if( !out.persons[declarer_id] ){
                    out.persons[declarer_id] = {
                      declarer_id : declarer_id,
                      declarer_fullname: e.fullname,
                      declarer_affectation: e.declarer_affectation,
                      total: 0,
                      unvalidated: 0
                    }
                  }
                  out.persons[declarer_id].total += 1;
                  if( validable ){
                    out.persons[declarer_id].unvalidated += 1;
                  }

                })
              }
              return out;
            }
        },

        methods:{

            fetch(){
                this.$http.get(this.url).then(
                    ok => {
                      this.synthesis = ok.data.synthesis;
                      console.log(ok);
                },
                    ko => {
                    console.log(ko)
                })
            }
        },

        mounted(){
            this.fetch();
        }

    }
</script>