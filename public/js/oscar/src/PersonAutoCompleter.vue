<template>
    <div>
        <input type="text" v-model="expression" @keyup.enter.prevent="search"/>
        <span v-show="loading">
            <i class="icon-spinner animate-spin"></i>
        </span>
        <div class="choose" style="position: absolute; z-index: 3000" v-show="persons.length > 0 && showSelector">
            <div class="choice" :key="c.id" v-for="c in persons" @click.prevent.stop="handlerSelectPerson(c)">
                <img :src="'https://www.gravatar.com/avatar/'+c.mailMd5+'?s=50'" :alt="c.isplayname">
                <div class="infos">
                    <strong>{{ c.displayname }}</strong><br>
                    <small>{{ c.affectation }}</small>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import OscarBus from './OscarBus.js';

    export default {
        data(){
            return {
                url: "/person?q=",
                persons: [],
                expression: "",
                loading: false,
                selectedPerson: null,
                showSelector: true
            }
        },
        watch: {
            expression(n, o){
                if( n.length >= 2 ){
                    this.search();
                }
            }
        },
        methods: {
            search(){
                this.loading = true;
                this.$http.get(this.url +this.expression +"*").then(
                    ok => {
                        this.persons = ok.body.datas;
                        this.showSelector = true;
                    },
                    ko => {
                        OscarBus.message('Erreur de recherche sur la personne', 'error');
                    }
                ).then( foo => this.loading = false );
            },
            handlerSelectPerson(data){
                this.selectedPerson = data;
                this.showSelector = false;
                this.expression = "";
                this.$emit('change', data);
            }
        }
    }
</script>