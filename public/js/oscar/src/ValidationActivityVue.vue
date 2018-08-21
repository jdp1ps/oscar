<template>
    <section>
        Déclaration

        <section class="period" v-for="datas,p in periods">
            <h2>{{ p }}</h2>
            <section class="main" v-for="activity in datas.main">
                <h3>
                    <strong>{{ activity.OscarId }}</strong>
                    <abbr title="">{{ activity.acronym }}</abbr>
                    {{ activity.label }}
                </h3>
                <section class="days">
                    <div class="label">&nbsp;</div>
                    <div class="day" v-for="i in nbrDays">
                        {{ i }}
                    </div>
                </section>
                <section v-for="lot, wpCode in activity.details">
                    <section class="days">
                        <div class="label">{{ lot.label }}</div>
                        <div class="day" v-for="i in nbrDays" :class="{'empty': !lot.days[i]}">
                            {{ lot.days[i] ? lot.days[i] : '0.0' }}
                        </div>
                    </section>
                </section>
            </section>

            <section class="otherProjects">
                <h3>Autres recherche</h3>
                <section v-for="otherProject in datas.projects">
                    <section class="days">
                        <div class="label">{{ otherProject.code }}</div>
                        <div class="day" v-for="i in nbrDays" :class="{'empty': !otherProject.days[i]}">
                            {{ otherProject.days[i] ? otherProject.days[i] : '0.0' }}
                        </div>
                    </section>
                </section>
            </section>

            <section class="other">
                <h3>Autres</h3>

                <section class="days" v-for="other in datas.others">
                    <div class="label">{{ other.label }}</div>
                    <div class="day" v-for="i in nbrDays" :class="{'empty': !other.days[i]}">
                        {{ other.days[i] ? other.days[i] : '0.0' }}
                    </div>
                </section>

            </section>

            <section class="total">
                <h3>Total pour cette période</h3>
                <section class="days">
                    <div class="label">Total</div>
                    <div class="day" v-for="i in nbrDays" :class="{'empty': !datas.total[i]}">
                        {{ datas.total[i] ? datas.total[i] : '0.0' }}
                    </div>
                </section>

            </section>
        </section>

    </section>
</template>
<style>
    .days:nth-child(odd){
        background-color: rgba(255,255,255,.2);
    }
    .days {
        border-bottom: thin rgba(255,255,255,.25) solid;
        background-color: rgba(255,255,255,.5);
        display: flex;}
        .days .day {
            color: black;
            font-weight: 600;
            flex: 1}
        .days .day {
            padding: .5em}
        .days .day.empty {
            font-weight: 100;
            color: rgba(0,0,0,.5);}
        .days .label {
            padding: .5em;
            font-size: 100%;
            color: black;
            flex: 0 0 150px;}
</style>
<script>
    // poi watch --format umd --moduleName  ValidationActivityVue --filename.css ValidationActivityVue.css --filename.js ValidationActivityVue.js --dist public/js/oscar/dist public/js/oscar/src/ValidationActivityVue.vue
    export default {
        props: {
            days: {
                default: 31
            },
            periods: {
                default: {}
            }
        },

        computed: {
            nbrDays(){
                let days = [];
                for( let i=1; i<= this.days; i++ ){
                    if( i < 10 ){
                        days.push('0'+i);
                    } else {
                        days.push(''+i);
                    }
                }
                return days;
            }
        },

        data(){
            return {
                foo: "default"
            }
        }
    }
</script>
