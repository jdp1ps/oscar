<template>
    <div>
        <template v-if="datas">
            <div v-for="p, period in datas.periods" v-if="datas" style="margin-bottom: 1em">
                    <table class="table-condensed table" >

                    <tr>
                        <th>{{ period | period }}</th>
                        <th v-for="wp in datas.workspackages">
                            {{ wp.code }}
                        </th>
                        <th>Total</th>
                    </tr>
                    <tbody>
                        <tr v-for="person in p.persons" v-if="person.total > 0">
                            <th>{{ person.displayname }}</th>
                            <td v-for="wp in person.workpackages">
                                <strong v-if="wp">{{ wp | duration}}</strong>
                                <small v-else>0.0</small>
                            </td>

                            <td class="total">
                                <strong v-if="person.total">{{ person.total | duration }}</strong>
                                <small v-else>0.0</small>

                            </td>
                        </tr>
                    </tbody>
                </table>
                <hr>
            </div>
        </template>



        <pre v-if="datas">{{ datas.persons }}</pre>
    </div>
</template>
<script>
    // poi watch --format umd --moduleName  ResumeActivity --filename.css ResumeActivity.css --filename.js ResumeActivity.js --dist public/js/oscar/dist public/js/oscar/src/ResumeActivity.vue
    export default {

        data(){
            return {
                datas: null
            }
        },

        methods: {
            fetch(){
                this.$http.get('').then(
                    ok => {
                        this.datas = ok.body
                    },
                    fail => {
                        console.log(fail)
                    }
                )
            }
        },

        mounted(){
            this.fetch();
        }
    }
</script>