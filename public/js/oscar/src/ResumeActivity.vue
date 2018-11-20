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
                    <tbody v-if="p.persons">
                        <tr v-for="person, personId in p.persons">
                            <th>{{ datas.persons[personId].displayname }} - {{ person }}</th>
                            <td v-for="wpCode in datas.workspackages">
                                <strong v-if="person.workpackages[wpCode]">{{ person.workpackages[wpCode] }}</strong>
                                <em v-else> ~ </em>
                                 (WPID: {{ wpCode.id }})
                            </td>
                            <td class="total">- {{ person.total }}</td>
                        </tr>
                    </tbody>
                        <tr>
                            <th>{{ period | period }}</th>
                            <th v-for="wp in datas.workspackages">
                                {{ wp.total }}
                            </th>
                            <th>Total</th>
                        </tr>
                </table>
                <pre>{{ p }}</pre>
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