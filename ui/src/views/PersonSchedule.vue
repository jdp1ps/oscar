<template>
    <section class="schedule">

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

        <div v-if="days">
          <p>La répartition horaire est issue de {{ from }}
            <strong v-if="from == 'application'">la configuration Oscar par défaut</strong>
            <strong v-if="from == 'sync'">la synchronisation (Connector)</strong>
            <strong v-if="from == 'custom'">la configuration prédéfinie</strong>
            <strong v-if="from == 'free'">la configuration manuelle</strong>
          </p>

          <article class="card xs" v-for="total, day in days">
            <h3 class="card-title">
              <strong>{{daysLabels[day]}}</strong>
              <input type="text" v-model="days[day]" v-if="editDay">
              <em class="big right" @click="handlerEditDays()" v-else>{{ $filters.duration(total) }}</em>
            </h3>
          </article>

          <article class="card">
            <h3 class="card-title">
              <strong>Total / semaine</strong>
              <em class="big right">{{ $filters.duration(totalWeek) }}</em>
            </h3>
          </article>

          <nav v-if="editable">
            <button @click.prevent="handlerEditDays()" class="btn btn-default" v-if="!editDay"><i class="icon-pencil"></i> modifier</button>
            <button @click.prevent="handlerSaveDays()" class="btn btn-primary" v-if="editDay"><i class="icon-floppy"></i> enregistrer</button>
            <select v-model="model" class="form-inline" v-if="models && editDay" @change="handlerSaveDays(model)">
              <option value="default">Aucun</option>
              <option v-for="m, key in models" :value="key" :selected="model == key">{{ m.label }}</option>
            </select>

            <button @click.prevent="handlerSaveDays('default')" class="btn btn-primary" v-if="editDay && from != 'default'"><i class="icon-floppy"></i> Horaires par défaut</button>
            <button @click.prevent="handlerCancel()" class="btn btn-primary" v-if="editDay"><i class="icon-cancel-circled"></i> annuler</button>
          </nav>

         </div>
    </section>
</template>
<script>
    import AxiosOscar from '../utils/AxiosOscar.js'

    export default {
        name: 'PersonSchedule',

        props: {
            urlapi: {default: ''},
            editable: { default: false },
            schedule: null
        },

        data() {
            return {
                daysLabels: {
                    '1': 'Lundi',
                    '2': 'Mardi',
                    '3': 'Mercredi',
                    '4': 'Jeudi',
                    '5': 'Vendredi',
                    '6': 'Samedi',
                    '7': 'Dimanche'
                },
                loading: null,
                error: null,
                dayLength: 0.0,
                from: null,
                days: null,
                editDay: null,
                newValue: 0,
                models: [],
                model: null
            }
        },

        computed: {
            totalWeek(){
                let total = 0.0;
                Object.keys(this.days).forEach(i => {
                    total += parseFloat(this.days[i]);
                });
                return total;
            }
        },

        methods: {
            day(index){
                if( this.days.hasOwnProperty(index) ){
                    return this.days[index];
                }
                return this.dayLength;
            },

            handlerEditDays(){
                this.editDay = true;
            },

            handlerCancel(){
                this.editDay = false;
                if( !this.urlapi ){
                    this.$emit('cancel');
                } else {
                    this.fetch();
                }
            },

            handlerSaveDays( model = 'input'){
                if( !this.urlapi ){
                    this.$emit('changeschedule', this.days);
                }
                else {
                    let datas = new FormData();
                    if( model == 'input' ){
                        datas.append('days', JSON.stringify(this.days));
                    }
                    else {
                        datas.append('model', model);
                    }


                    AxiosOscar.post(this.urlapi, datas).then(
                        ok => {
                            this.fetch();
                        }
                    ).then(foo => {
                        this.loading = false
                    });
                }
            },


            fetch(clear = true) {
                if( this.schedule == null ){
                    AxiosOscar.get(this.urlapi, {
                      pendingMsg: "Chargement de la répartition horaire",
                      pendingBack: true
                    }).then(
                        ok => {
                            this.days = ok.data.days;
                            this.dayLength = ok.data.dayLength;
                            this.from = ok.data.from;
                            this.models = ok.data.models;
                            this.model = ok.data.model;
                        }
                    );
                } else {
                    this.days = this.schedule.days;
                    this.dayLength = this.schedule.dayLength;
                    this.editDay = true;
                }
            }
        },

        mounted() {
          this.fetch(true)
        }
    }
</script>