<template>
    <div>

        <section v-if="declarations">

            <a href="#" class="btn btn-xs btn-default" @click="group = 'label'">
                par type
            </a>
            <a href="#" class="btn btn-xs btn-default" @click="group = 'monthLabel'">
                par période
            </a>
            <section v-for="group, label in grouped" class="card card-xs">
                <h1>{{ label }}</h1>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ label }}</th>
                            <th v-for="i in group.maxDays">
                                {{i }}
                            </th>
                            <th>
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in group.declarations">
                            <th>{{ d.sublabel }}</th>
                            <td v-for="i in group.maxDays">
                                <strong v-if="d.days[i]">{{ d.days[i].total }}</strong>
                                <em v-else>-</em>
                            </td>
                            <td>
                                <button class="btn btn-success btn-xs" @click="validate(d.period_id)">Valider</button>
                                <button class="btn btn-danger btn-xs" @click="reject(d.period_id)">Rejeter</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </section>
            <!--
            <article v-for="p in declarations">
                <h2>{{ p.label }} ({{ p.monthLabel }})</h2>
                <p v-if="p.totaltimesheets == 0" class="alert alert-info">
                    Il n'y pas de créneaux à déclarer
                </p>
                <section v-else>
                    <p class="help">Il y'a {{ p.totaltimesheets }} créneaux à valider.</p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th v-for="i in p.totalDays">{{i}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td v-for="i in p.totalDays">
                                <strong v-if="p.days[i]">{{ p.days[i].total }}</strong>
                                <em v-else>0.0</em>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </section>

            </article>-->
        </section>
    </div>
</template>
<script>
    // Compilation :
    // poi watch --format umd --moduleName  ValidationPeriod --filename.css ValidationPeriod.css --filename.js ValidationPeriod.js --dist public/js/oscar/dist public/js/oscar/src/ValidationPeriod.vue
    export default {
        data() {
            return {
                error: null,
                declarations: null,
                group: 'monthLabel' // ou label
            }
        },

        props: {
            bootbox: { required: true }
        },

        computed: {
            subgroup(){
                return this.group == "monthLabel" ? "label" : "monthLabel"
            },
            grouped (){
                let datas = {};
                if( this.declarations ){
                    this.declarations.forEach(item => {
                        let key = item[this.group];
                        console.log(key);
                        if( !datas.hasOwnProperty(key) ){
                            datas[key] = {
                                label: item[key],
                                declarations: [],
                                maxDays: 0
                            }
                        }
                        item.sublabel = item[this.subgroup];
                        datas[key].declarations.push(item);
                        datas[key].maxDays = Math.max(datas[key].maxDays, item.totalDays);
                    });
                }


                return datas;
            }
        },

        methods: {
            fetch(){
                this.$http.get().then(
                    ok => {
                        console.log(ok);
                        this.declarations = ok.body.packages;
                    },
                    ko => {
                        console.log("ERROR", ko);
                    }
                )
            },

            validate( period_id ){
                this.bootbox.confirm("Valider cette déclaration ?", ok => {
                    if( ok ){
                        this.send('valid', period_id);
                    }
                });
            },

            reject( period_id ){
                this.bootbox.confirm("Refuser cette déclaration ?", ok => {
                        this.send('reject', period_id);
                });
            },

            send(action, period_id){
                let dataSend = new FormData();
                dataSend.append('period_id', period_id);
                dataSend.append('action', action);
                this.$http.post('', dataSend).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = "Erreur : " + ko.bodyText;
                    }
                );
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>