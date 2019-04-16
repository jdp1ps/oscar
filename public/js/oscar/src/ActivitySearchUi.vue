<template>
    <div>
        <transition name="fade">
            <div class="vue-loader" v-show="loaderMsg">
                <div class="content-loader">
                    <i class="icon-spinner animate-spin"></i>
                    {{ loaderMsg }}
                </div>
            </div>
        </transition>

        <oscargrowl />
        <h1>
            <i class="icon-cube"></i>
            {{ title }}
        </h1>

        <form action="" @submit.prevent="handlerSubmit">
            <div class="input-group input-group-lg">
                <input placeholder="Rechercher dans l'intitulé, code PFI...…" class="form-control input-lg" name="q" v-model="search" type="search">

                <span class="input-group-btn">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </span>
            </div>
        </form>

        <section v-if="search !== null">
            <h2 class="text-right">{{ totalResultQuery }} résultat(s)</h2>
            <transition-group name="list" tag="div">
                <activity :activity="activity" v-for="activity in activities" :key="activity.id" />
            </transition-group>
        </section>
    </div>
</template>
<script>
    import ActivitySearchItem from './ActivitySearchItem.vue';
    import OscarGrowl from './OscarGrowl.vue';
    import OscarBus from './OscarBus.js';

    //node node_modules/.bin/poi watch --format umd --moduleName  ActivitySearchUi --filename.css ActivitySearchUi.css --filename.js ActivitySearchUi.js --dist public/js/oscar/dist public/js/oscar/src/ActivitySearchUi.vue


    export default {
        props: {
            url: { required: true },
            first: { required: true, typ: Boolean },
            title: { default: "Activités de recherche" }
        },

        components: {
            activity: ActivitySearchItem,
            oscargrowl: OscarGrowl,
        },

        data() {
            return {
                loaderMsg: "",
                page: 0,
                totalPages: 0,
                totalResultQuery: 0,
                previous: null,
                search: null,
                activities: []
            }
        },
        methods: {
            handlerSubmit(){
                this.performSearch(this.search, 1, 'Recherche...')
            },

            performSearch(what, page, msg){
                this.loaderMsg = msg;

                this.search = what === null ? '' : what;

                this.$http.get(this.url +"?q=" +this.search +"&p=" +page).then(
                    (ok) => {
                        if( ok.body.page == 1 ) {
                            this.activities = [];
                        }
                        this.activities = this.activities.concat(ok.body.datas.content);
                        this.totalResultQuery = ok.body.totalResultQuery;
                        this.totalPages = ok.body.totalPages;
                        this.page = ok.body.page;
                    },
                    (ko) => {
                        OscarBus.message("Impossible de charger le résultat de la recherche !", 'error');
                        this.activities = [];
                        this.totalResultQuery = 0;
                        this.totalPages = 0;
                        this.page = 0;
                    }
                ).then( foo => {
                    this.loaderMsg = "";
                });
            },

            loadNextPage(){
                if( this.page < this.totalPages ){
                    this.page++;
                    this.performSearch(this.search, this.page, 'Chargement de la page ' +this.page +"/" +this.totalPages);
                }
            }
        },

        mounted(){

            this.handlerSubmit();

            window.onscroll = () => {
                let bottomOfWindow = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;

                if (bottomOfWindow) {
                    this.loadNextPage();
                }
            };
        }
    }
</script>
