<template>
    <div class="widget" :class="{ 'loading': loading }">

        <div class="widget-loading-message">
            {{ loading }}
        </div>

        <section class="widget-content">
            <article class="card" v-for="s in sections">
                <h3>{{ s.label }}</h3>
                <nav class="bottom-options">
                    <a href="#" @click="handlerDelete(s)">Supprimer</a>
                    <a href="#" @click="handlerEdit(s)">Modifier</a>
                </nav>
            </article>
        </section>

        <div class="overlay" v-if="deleteData">
            <div class="overlay-content">
                <h3>Supprimer la section <strong>{{ deleteData.label }}</strong> ?</h3>
                <nav class="admin-bar">
                    <a href="#" class="btn btn-default" @click.prevent="deleteData= null">
                        <i class="icon-cancel-circled"></i>
                        Annuler
                    </a>
                    <a href="#" class="btn btn-primary" @click="performDelete(deleteData)">
                        Supprimer
                    </a>

                </nav>
            </div>
        </div>

        <div class="overlay" v-if="formData">
            <div class="overlay-content">
                <div class="form-group">
                    <label for="label"></label>
                    <input type="text" v-model="formData.label" class="form-control input-lg" id="label" />
                </div>
                <nav class="admin-bar">
                    <a href="#" class="btn btn-default" @click.prevent="formData = null">
                        <i class="icon-cancel-circled"></i>
                        Annuler
                    </a>
                    <a href="#" class="btn btn-primary" @click="handlerSave()">
                        Enregistrer
                    </a>

                </nav>
            </div>
        </div>

        <nav class="admin-bar">
            <button @click="handlerNew()" class="btn btn-primary">Nouvelle section</button>
            <button @click="fetch()" class="btn btn-default">Recharger</button>
        </nav>

    </div>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  DocumentSectionAdmin --filename.js DocumentSectionAdmin.js --dist public/js/oscar/dist public/js/oscar/src/DocumentSectionAdmin.vue

    export default {
        props: {
            url: {
                required: true
            }
        },

        components: {

        },


        data(){
            return {
                formData: null,
                sections:[],
                deleteData: null,
                loading: "Chargement des données"
            };
        },

        computed: {

        },


        methods: {
            handlerSave(){
                this.loading = "Enregistrement des données";
                let data = new FormData();
                data.append('label', this.formData.label);
                data.append('id', this.formData.id);
                this.formData = null;
                this.$http.post(this.url, data).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo => {

                    this.loading = "";
                })
            },

            handlerNew(){
              this.formData = {
                  id: "",
                  label: "Nouvelle section"
              }
            },

            handlerEdit(section){
                this.formData = JSON.parse(JSON.stringify(section));
            },

            handlerDelete(section){
                this.deleteData = section;
            },

            performDelete(section){
                this.loading = "Suppression en cours";
                let data = new FormData();
                data.append('id', section.id);
                data.append('action', 'delete');
                this.deleteData = null;

                this.$http.post(this.url, data).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo => {

                    this.loading = "";
                });
            },

            fetch(){
                this.loading = "Chargement des données";
                this.$http.get(this.url).then(
                    ok => {
                        this.sections = ok.body.sections
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo => this.loading = "" )

            }
        },

        mounted(){
            this.fetch();
        }
    }

</script>