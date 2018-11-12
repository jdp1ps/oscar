<template>
    <div>
        <div class="overlay" v-if="error"  style="z-index: 2002">
            <div class="content container overlay-content">
                <h2><i class="icon-attention-1"></i> Oups !</h2>
                <pre class="alert alert-danger">{{ error }}</pre>
                <p class="text-danger">
                    Si ce message ne vous aide pas, transmettez le à l'administrateur Oscar.
                </p>
                <nav class="buttons">
                    <button class="btn btn-primary" @click="error = ''">Fermer</button>
                </nav>
            </div>
        </div>
        <section v-if="declarations && declarations.length">
            <a href="#" class="btn btn-xs" :class="group == 'person' ? 'btn-primary' : 'btn-default'" @click="group = 'person'">
                par personne
            </a>
            <a href="#" class="btn btn-xs" :class="group == 'monthLabel' ? 'btn-primary' : 'btn-default'" @click="group = 'monthLabel'">
                par période
            </a>
            <section v-for="group, label in grouped" class="card card-xs">
                <h1>{{ label }}</h1>
                <table class="table table-bordered validations-table">
                    <thead>
                        <tr>
                            <th>{{ label }}</th>
                            <th>Type</th>
                            <th>
                               ~
                            </th>
                            <th v-for="i in group.maxDays">
                                {{i }}
                            </th>
                            <th>Total</th>
                            <th>
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in group.declarations">
                            <th>{{ d.sublabel }}</th>
                            <th>{{ d.label }}</th>
                            <td>
                                <small v-for="total,lot in d.totalWps" class="lot"><i class="icon-archive"></i>{{ lot }}</small>
                                <strong class="lotsTotal">Total</strong>
                            </td>
                            <td v-for="i in group.maxDays">
                                <span v-if="d.days[i]">
                                    <small v-for="detail, wp in d.days[i].details" :title="'Total pour le lot : ' + wp" class="lot">
                                        {{ detail | duration }}
                                    </small>
                                    <strong class="lotsTotal">
                                        {{ d.days[i].total | duration }}
                                    </strong>
                                </span>
                                <em v-else>-</em>
                            </td>

                            <td>
                                <small v-for="total,lot in d.totalWps" class="lot"><i class="icon-archive"></i>{{ total | duration }}</small>
                                <strong class="lotsTotal">{{ d.total | duration }}</strong>
                            </td>
                            <td>
                                <span v-if="d.validation.status == 'valid'">
                                    <i class="icon-ok-circled"></i> Validé
                                </span>
                                <span v-else-if="d.validation.status == 'conflict'">
                                    <i class="icon-minus-circled"></i> Refusé
                                </span>
                                <span v-else>
                                    <button class="btn btn-success btn-xs" @click="validate(d.period_id)">Valider</button>
                                    <button class="btn btn-danger btn-xs" @click="reject(d.period_id)">Rejeter</button>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </section>
        <section v-else class="alert alert-info">
            Aucune déclaration en attente
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
                return this.group == "monthLabel" ? "person" : "monthLabel"
            },
            grouped (){
                let datas = {};
                if( this.declarations ){
                    this.declarations.forEach(item => {
                        let key = item[this.group];

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
                        this.send('valid', period_id, '');
                    }
                });
            },

            reject( period_id ){
                this.bootbox.prompt("Refuser cette déclaration ?", ok => {
                    if( ok )
                        this.send('reject', period_id, ok);
                });
            },

            send(action, period_id, message){
                let dataSend = new FormData();
                dataSend.append('period_id', period_id);
                dataSend.append('action', action);
                dataSend.append('message', message);
                this.$http.post('', dataSend).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = "Erreur : " + ko.body;
                    }
                );
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>