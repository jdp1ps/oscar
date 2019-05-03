<template>
    <div>
        <h1>SECTION</h1>

        <article class="card" v-for="s in sections">
            <h3>{{ section.label }}</h3>
            <a href="#" @click="handlerDelete(s)">Supprimer</a>
            <a href="#" @click="handlerEdit(s)">Modifier</a>
        </article>

        <div class="overlay" v-if="formData">
            <div class="overlay-content">

                    <input type="text" v-model="formData.label" class="input-lg">
                <a href="#" class="btn btn-primary" @click="handlerSave()">
                    Enregistrer
                </a>
            </div>
        </div>

        <button @click="handlerNew()">Nouvelle section</button>
        <pre>{{ $data }}</pre>
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
                sections:[]
            };
        },

        computed: {

        },


        methods: {
            handlerSave(){
                let data = new FormData();
                data.append('label', this.formData.label);
                this.$http.post(this.url, data).then(
                    ok => {
                        console.log("OK");
                        this.fetch();
                    },
                    ko => {
                        console.log("ERROR", ko);
                    }
                )
            },

            handlerNew(){
              this.formData = {
                  id: null,
                  label: "Nouvelle section"
              }
            },
            fetch(){
                this.$http.get(this.url).then(
                    ok => {
                        console.log(ok.body)
                        this.sections = ok.body.sections
                    },
                    ko => {
                        console.error(ko)
                    }
                )
            }
        },

        mounted(){
            this.fetch();
        }
    }

</script>