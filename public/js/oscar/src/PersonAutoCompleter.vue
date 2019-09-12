<template>
    <div>
        <input type="text" v-model="expression" @keyup.enter.prevent="search"/>
        <span v-show="loading">
            <i class="icon-spinner animate-spin"></i>
        </span>
        <div class="choose" style="position: absolute; z-index: 3000; max-height: 400px; overflow: hidden; overflow-y: scroll" v-show="persons.length > 0 && showSelector">
            <div class="choice" :key="c.id" v-for="c in persons" @click.prevent.stop="handlerSelectPerson(c)">
                <div style="display: block; width: 50px; height: 50px" >
                    <img :src="'https://www.gravatar.com/avatar/'+c.mailMd5+'?s=50'" :alt="c.displayname" style="width: 100%" />

                </div>
                <div class="infos">
                    <strong style="font-weight: 700; font-size: 1.1em; padding-left: 0">{{ c.displayname }}</strong><br>
                    <span style="font-weight: 100; font-size: .8em; padding-left: 0"><i class="icon-location"></i>
                        {{ c.affectation }}
                        <span v-if="c.ucbnSiteLocalisation"> ~ {{ c.ucbnSiteLocalisation }}</span>
                    </span><br>
                    <em style="font-weight: 100; font-size: .8em"><i class="icon-mail"></i>{{ c.email }}</em>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    //import OscarBus from './OscarBus.js';

    export default {
        data(){
            return {
                url: "/person?l=m&q=",
                persons: [],
                expression: "",
                loading: false,
                selectedPerson: null,
                showSelector: true,
                request: null
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
                this.$http.get(this.url +this.expression, {
                    before( r ){
                        if( this.request ){
                            this.request.abort();
                        }
                        this.request = r;
                    }
                }).then(
                    ok => {
                        this.persons = ok.body.datas;
                        this.showSelector = true;
                    },
                    ko => {
                        // OscarBus.message('Erreur de recherche sur la personne', 'error');
                    }
                ).then( foo => {
                    this.loading = false;
                    this.request = null;
                });
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