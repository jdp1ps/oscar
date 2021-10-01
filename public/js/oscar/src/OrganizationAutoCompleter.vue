<template>
    <div>
        <input type="text" v-model="expression" @keyup.enter.prevent="search"/>
        <span v-show="loading">
            <i class="icon-spinner animate-spin"></i>
        </span>
        <div class="choose" style="position: absolute; z-index: 3000; max-height: 200px; overflow: scroll; overflow-y: scroll; overflow-x: hidden" v-show="organizations.length > 0 && showSelector">
            <div class="choice" :key="c.id" v-for="c in organizations" @click.prevent.stop="handlerSelectPerson(c)">
                <div class="infos">
                    <strong>{{ c.label }}</strong>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import OscarBus from './OscarBus.js';

  let tempo;

    export default {
        data(){
            return {
                url: "/organization?q=",
                organizations: [],
                expression: "",
                loading: false,
                selectedPerson: null,
                showSelector: true
            }
        },
        watch: {
            expression(n, o){
                if( n.length >= 2 ){
                    if(tempo){
                      clearTimeout(tempo);
                    }
                    tempo = setTimeout( () => {
                      this.search();
                    }, 500)
                }
            }
        },
        methods: {
            search(){
                this.loading = true;
                this.$http.get(this.url +this.expression).then(
                    ok => {
                        this.organizations = ok.body.datas;
                        this.showSelector = true;
                    },
                    ko => {
                        console.log(ko);
                        //OscarBus.message('Erreur de recherche sur la personne', 'error');
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