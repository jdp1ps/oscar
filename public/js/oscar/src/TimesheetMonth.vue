<template>
    <section>
        <h1>Feuille de temps mensuelle</h1>
        <section v-if="ts">
            <h2>Déclarations de temps pour <strong>{{ ts.person }}</strong></h2>
            <h3>Période du XXXX au XXXX</h3>

            <article>
                <div class="line">
                    <div class="day" v-for="d in ts.days">
                        {{d | dayText}}
                    </div>
                </div>
            </article>
        </section>
        <pre>{{ ts }}</pre>

    </section>
</template>

<script>
    let defaultDate = new Date();
    let moment = function(){};

    export default {
        props: {
            month: { default: defaultDate.getMonth() },
            year: { default: defaultDate.getFullYear() },
            moment: { required: true }
        },

        data(){
            return {
                ts: null
            }
        },

        filters: {
            dayText(value){
                var m = moment();
                return "Day " + m.format('YYYYY MMM dddd');
            }
        },

        methods: {
            fetch(){
                console.log("Chanrgement des données");
                this.$http.get('?month=' +this.month +'&year=' + this.year).then(
                    ok => {
                        console.log(ok);
                        this.ts = ok.body
                    },
                    ko => {

                    }
                )
            }
        },
        mounted(){
            moment = this.moment;
            this.fetch()
        }
    }
</script>