<template>
    <section>
        <ajaxfail :error="error"></ajaxfail>
        <h1>Liste des déclarants</h1>
        <section>
            <article class="card card-xs" v-for="d in declarers">
                <h2 class="card-title">
                    <span><i class="icon-user"></i>
                        <em class="firstname">{{ d.firstName }}</em>
                        <strong>{{ d.lastName }}</strong>
                        <span class="circle">
                            <i class="icon-archive"></i>
                            {{ d.workpackages }}
                        </span>
                    </span>
                    <span><i class="icon-mail"></i>{{ d.email }}</span>
                    <small>{{ d.affectation }}</small>
                </h2>

                <section class="periods">
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <th>Période</th>
                            <th v-for="m in months">{{ m }}</th>
                        </tr>
                        <tr v-for="mois,annee in pack(d)">
                            <th>{{ annee }}</th>
                            <td v-for="m, mm in months">
                                <span v-if="d.periods[annee+'-'+m] == null">~</span>
                                <span v-else style="background: #8f97a0">

                                    {{ d.periods[annee+'-'+m].length ? 'X' : 'non' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </section>

            </article>
        </section>
    </section>
</template>
<script>
    //  poi watch --format umd --moduleName  DeclarersList --filename.css DeclarersList.css --filename.js DeclarersList.js --dist public/js/oscar/dist public/js/oscar/src/DeclarersList.vue

    import AjaxFail from './AjaxFail.vue';

    export default {
        data(){
            return {
                error: null,
                declarers: [],
                months: ['01','02', '03','04','05', '06','07','08', '09','10','11', '12']
            }
        },

        components: {
            'ajaxfail': AjaxFail,
        },

        methods: {
            fetchDeclarer(){
                this.$http.get('?a=declarers').then(
                     ok => {
                        this.declarers = ok.body.declarers;
                     },
                     ko => {
                        this.error = ko;
                     }
                );
            },
            pack(t){
                let out = {};
                let keys = Object.keys(t.periods);


                for( let i=0;  i<keys.length; i++ ){
                    let split = keys[i].split('-');
                    let year = split[0];
                    let month = split[1];
                    if( !out.hasOwnProperty(year) ){
                        out[year] = {};
                    }
                    if( !out[year].hasOwnProperty(month) ){
                        out[year][month] = 'X';
                    }
                }

                return out;
            }
        },
        mounted(){
            this.fetchDeclarer();
        }
    }
</script>