<template>
    <section style="background: red">
        <h1>DOCUMENT version 2.0</h1>
        <p>TOTO TATA</p>

        <!--<transition name="fade">-->
            <!--<div class="overlay" v-if="formData">-->
                <!--UPLOAD-->
            <!--</div>-->
        <!--</transition>-->

        <!--<transition name="fade">-->
            <!--<div class="overlay" v-if="error">-->
                <!--<div class="alert alert-danger">-->
                    <!--<h3>Erreur-->
                        <!--<a href="#" @click.prevent="error =null" class="float-right">-->
                            <!--<i class="icon-cancel-outline"></i>-->
                        <!--</a>-->
                    <!--</h3>-->
                    <!--<p>{{ error }}</p>-->
                <!--</div>-->
            <!--</div>-->
        <!--</transition>-->

        <!--<transition name="fade">-->
            <!--<div class="overlay" v-if="deleteData">-->
                <!--<div class="alert alert-danger">-->
                    <!--<h3>Supprimer ? ?</h3>-->
                    <!--&lt;!&ndash;-->
                    <!--<nav>-->
                        <!--<button type="reset" class="btn btn-danger" @click.prevent="deleteData = null">-->
                            <!--<i class="icon-cancel-outline"></i>-->
                            <!--Annuler-->
                        <!--</button>-->
                        <!--<button type="submit" class="btn btn-success" @click.prevent="performDelete">-->
                            <!--<i class="icon-ok-circled"></i>-->
                            <!--Confirmer-->
                        <!--</button>-->
                    <!--</nav>-->
                    <!--&ndash;&gt;-->
                <!--</div>-->
            <!--</div>-->
        <!--</transition>-->

        <!--<article v-for="d in documents" class="card">-->
            <!--<h3 class="card-title">-->
                <!--<span>-->
                    <!--[{{d.id}}]-->
                    <!--{{ d.label }}-->
                <!--</span>-->
            <!--</h3>-->
            <!--<div class="card-content">-->
                <!--content-->
            <!--</div>-->
        <!--</article>-->
        <!--<hr>-->
        <!--<button type="button" class="btn btn-primary" @click.prevent="handlerNew">-->
            <!--<i class="icon-plus-circled"></i>-->
            <!--Nouveau document-->
        <!--</button>-->
    </section>
</template>
<script>

    // nodejs node_modules/.bin/poi watch --format umd --moduleName  DocumentsActivity --filename.js DocumentsActivity.js --dist public/js/oscar/dist public/js/oscar/src/DocumentsActivity.vue

    export default {
        props: {
            disc: {
                default: []
            },
            url: {
                default: ""
            }
        },
        data(){
            return {
                formData: null,
                error: null,
                deleteData: null,
                documents: null
            }
        },

        computed:{
            documentsPacked(){
                return this.documents;
            }
        },

        methods:{
//            handlerSubmit(){
//                let data = new FormData(), send;
//                data.append('label', this.formData.label);
//                if( this.formData.id ){
//                    data.append('id', this.formData.id);
//                    send = this.$http.post('?', data);
//                    send.then(ok => {
//
//                        for( let i=0; i<this.disciplines.length; i++ ){
//                            if( this.disciplines[i].id == this.formData.id ){
//                                this.disciplines[i].label = ok.body.discipline.label;
//                            }
//                        }
//
//                    })
//                } else {
//                    send = this.$http.put('?', data);
//                    send.then(ok => {
//                        this.disciplines.push(ok.body.discipline);
//                        this.disciplines.sort( (a, b) => (a.label < b.label ? -1 : (a.label == b.label ? 0 : 1)));
//                    })
//                }
//
//                send.catch(
//                    ko => {
//                        this.error = ko.body;
//                    }
//                ).finally( foo => {
//                    console.log('FINAL');
//                    this.formData = null;
//                });
//            },
//
//            handlerNew(){
//                this.formData = {
//                    id: null,
//                    label: "",
//                    original: null
//                };
//            },
//
//            handlerEdit( discipline ){
//                this.formData = {
//                    id: discipline.id,
//                    label: discipline.label,
//                    original: discipline.label
//                };
//            },
//
//            handlerCancelForm(){
//                this.formData = null;
//            },
//
//
//            handlerDelete( discipline ){
//                this.deleteData = discipline;
//            },
//
//            performDelete(){
//                let discipline = this.deleteData;
//                this.$http.delete('?id=' + discipline.id).then(
//                    ok => {
//                        let disciplines = [];
//
//                        this.disciplines.forEach( item => {
//                            if( item.id != discipline.id )
//                                disciplines.push(item);
//                        });
//
//                        this.disc = disciplines;
////                        this.deleteData = null;
//                    },
//                    ko => {
//                        this.error = ko.body;
//                    }
//                ).then( foo => this.deleteData = null );
//            }
        },
        mounted(){
            console.log("DEBUG");
        }
    }
</script>