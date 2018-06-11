<template>
    <div>
        <input type="text" v-model="expression" @keyup.enter.prevent="search"/>
        <span v-show="loading">
            <i class="icon-spinner animate-spin"></i>
        </span>
        <div class="choose" style="position: absolute">
            <div class="choice" :key="c.id" v-for="c in persons">
                <img :src="'https://www.gravatar.com/avatar/'+c.mailMd5+'?s=50'" :alt="displayname">
                <div class="infos">
                    <strong>{{ c.displayname }}</strong><br>
                    <small>{{ c.affectation }}</small>
                </div>
            </div>
        </div>
        <pre>{{ expression }} {{ persons }}</pre>
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
                loading: false
            }
        },
        watch: {
            expression(n, o){
                console.log(n,o);
            }
        },
        methods: {
            search(){
                this.loading = true;
                this.$http.get(this.url +this.expression).then(
                    ok => {
                        console.log(ok.body);
                        this.persons = ok.body.datas;
                    },
                    ko => {
                        OscarBus.message('Erreur de recherche sur la personne', 'error');
                    }
                ).then( foo => this.loading = false );
            }
        }
    }
</script>
<style>
    .choice {
        display: flex;
        flex-direction: row;
        align-items: stretch;
        width: 100%;
        background: white;
    }
    .choice > img {
        flex: 0 0 50px;
    }
    .search-item .infos {
        font-size: 12px;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        width: 100%;
        margin: 0;
        padding: 0;
        padding-left: 1em;
    }
    .search-item .infos small, .search-item .infos strong {
        padding: 0 1em;
    }
    .search-item .infos small {
        font-size: 8px;
        font-weight: 100;
    }
</style>
