<template>
    <section>

        <h1>Liste du personnel</h1>
        <div class="input-group input-group-lg">
            <span class="input-group-addon"><i class="icon-zoom-in-outline"></i></span>
            <input type="text" class="form-control" placeholder="Rechercher" @keyup.enter="handlerSearch" v-model="search" />
            <span class="input-group-addon" v-show="loading">
                <span class="oscar-text-loading">
                    <i class="icon-spinner animate-spin"></i>
                    Chargement des données
                </span>
            </span>

            <span class="input-group-btn" v-if="page > 0">
                <span :class="page == 1 ? 'disabled' : ''" class="btn btn-default" @click="handlerPrevious">
                    <i class="icon-left-open-outline"></i>
                </span>

                <span class="btn btn-default disabled">
                    <i class="icon-docs"></i>
                    <strong>{{ page }}</strong> / <em>{{ totalPage }}</em>
                </span>

                <span class="btn btn-default disabled">
                    <i class="icon-group"></i>
                    <strong>{{ total }}</strong> personne(s)
                </span>

                <span :class="page < totalPage ? '' : 'disabled'" class="btn btn-default"  @click="handlerNext">
                    <i class="icon-right-open-outline"></i>
                </span>
            </span>
        </div>

        <div class="alert alert-danger" v-if="error">
            <div class="close" @click="error=null">X</div>
            {{ error }}
        </div>

        <hr>

        <article v-for="person in persons" class="card xs">
            <h2 class="card-title" style="display: flex">
                <img class="thumb32" :src="'//www.gravatar.com/avatar/' + person.mailMd5 +'?s=30'" alt="">
                <span class="fn">
                    <span class="family-name">{{ person.lastName }}</span>
                    <span class="given-name">{{ person.firstName }}</span>
                    <span class="label label-primary">{{  person.activities }}</span>
                    <a :href="'/person/show/' + person.id" class="more">Fiche</a>
                </span>

                <span class="right">
                    <small v-if="person.coworker">
                        <i class="icon-building-filled"></i> collègue
                    </small>

                    <small v-if="person.sub">
                        <i class="icon-user-md"></i> Subordonné
                    </small>
                </span>
            </h2>

            <section v-if="person.organisations">
                <span v-for="or in person.organisations" class="cartouche">
                    {{ or.organisation }}
                    <span class="addon">
                        {{ or.roles.join(', ') }}
                    </span>
                </span>
            </section>

            <div class="card-content text-highlight">
                <i class="icon-phone-outline"></i><strong class="addon">{{ person.phone }}</strong> |
                Affectation LDAP : <strong class="addon">{{ person.affectation }}</strong> |
                Localisation LDAP : <strong class="addon">{{ person.ucbnSiteLocalisation }}</strong> |
                <i class="icon-mail"></i><strong class="addon">{{ person.mail }}</strong>
            </div>

        </article>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  PersonsList --filename.css PersonsList.css --filename.js PersonsList.js --dist public/js/oscar/dist public/js/oscar/src/PersonsList.vue
    export default {
        props: {
            urlapi: {
                default: ""
            }
        },

        data(){
            return {
                search: "",
                loading: false,
                total: 0,
                page: 0,
                error: "",
                persons: []
            }
        },

        computed:{
            totalPage(){
                return Math.ceil(this.total / 50);
            }
        },


        methods: {
            handlerPrevious(){
                if( this.page > 1 ){
                    this.page--;
                    this.handlerSearch(false);
                }
            },

            handlerNext(){
                if( this.page < this.totalPage ){
                    this.page++;
                    this.handlerSearch(false);
                }
            },

            handlerSearch(reset=true){
                this.loading = true;
                let page = this.page > 0 ? this.page : 1;

                if( reset ){
                    page = 1;
                }

                this.$http.get(this.urlapi + "?p=" +page +"&q=" + this.search).then(
                    ok => {
                        this.persons = ok.body.persons;
                        this.page = ok.body.page;
                        this.total = ok.body.total;
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then(ko => {
                    this.loading = false;
                });
            }
        }
    }
</script>