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


        <article class="card xs" v-for="total, day in days">
            <h3 class="card-title">
                <strong>{{daysLabels[day]}}</strong>
                <input type="text" v-model="days[day]" v-if="editDay">
                <em class="big right" @click="handlerEditDays()" v-else>{{ total | heures }}</em>
            </h3>
        </article>

        <article class="card">
            <h3 class="card-title">
                <strong>Total / semaine</strong>
                <em class="big right">{{ totalWeek | heures }}</em>
            </h3>
        </article>

        <nav v-if="editable">
            <button @click.prevent="handlerEditDays()" class="btn btn-default" v-if="!editDay"><i class="icon-pencil"></i> modifier</button>
            <button @click.prevent="handlerSaveDays()" class="btn btn-primary" v-if="editDay"><i class="icon-floppy"></i> enregistrer</button>
            <button @click.prevent="fetch()" class="btn btn-primary" v-if="editDay"><i class="icon-cancel-circled"></i> annuler</button>
        </nav>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  PersonSchedule --filename.css PersonSchedule.css --filename.js PersonSchedule.js --dist public/js/oscar/dist public/js/oscar/src/PersonSchedule.vue
    import AjaxResolve from "./AjaxResolve";

    export default {
        name: 'PersonSchedule',

        props: {
            urlapi: {default: ''},
            editable: { default: false }
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
                days: {},
                editDay: null,
                newValue: 0
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

            handlerSaveDays(){
                this.loading = "Enregistrement des horaires";
                let datas = new FormData();
                datas.append('days', JSON.stringify(this.days));

                this.$http.post(this.urlapi, datas).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de modifier les horaires', ko);
                    }
                ).then(foo => {
                    this.loading = false
                });
            },


            fetch(clear = true) {
                this.loading = "Chargement des données";

                this.$http.get(this.urlapi).then(
                    ok => {
                        console.log(ok.body);
                        this.days = ok.body.days;
                        this.dayLength = ok.body.dayLength;
                    },
                    ko => {
                        this.error = AjaxResolve.resolve('Impossible de charger les données', ko);
                    }
                ).then(foo => {
                    this.loading = false;
                    this.editDay = null;
                });
            }
        },

        mounted() {
            this.fetch(true)
        }
    }
</script>