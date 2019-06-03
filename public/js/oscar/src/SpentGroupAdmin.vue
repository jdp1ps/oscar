<template>
    <section>
        <h1>Type de dépense</h1>

        <transition name="fade">
            <div class="overlay" v-if="formData">
                <form action="" @submit.prevent="handlerSubmit">
                    <h1 v-if="formData.original">Modifier {{ formData.original }}</h1>
                    <h1 v-else="formData.original">Nouvelle discipline</h1>
                    <label for="form_label">Intitulé</label>
                    <input type="text" class="form-control lg" v-model="formData.label" id="form_label" />
                    <hr>
                    <nav>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-floppy"></i>
                            Enregistrer
                        </button>
                        <button type="reset" class="btn btn-default" @click.prevent="handlerCancelForm">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </form>
            </div>
        </transition>

        <transition name="fade">
            <div class="overlay" v-if="error">
                <div class="alert alert-danger">
                    <h3>Erreur
                        <a href="#" @click.prevent="error =null" class="float-right">
                            <i class="icon-cancel-outline"></i>
                        </a>
                    </h3>
                    <p>{{ error }}</p>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="overlay" v-if="deleteData">
                <div class="alert alert-danger">
                    <h3>Supprimer la discipline <strong>{{ deleteData.label }}</strong> ?</h3>
                    <nav>
                        <button type="reset" class="btn btn-danger" @click.prevent="deleteData = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-success" @click.prevent="performDelete">
                            <i class="icon-ok-circled"></i>
                            Confirmer
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <article v-for="d in spentgroups" class="card">
            <h3 class="card-title">
                <span>
                    [{{d.id}}]
                    {{ d.label }}
                </span>
                <small>
                    <a href="#" @click.prevent="handlerEdit(d)">
                        <i class="icon-pencil"></i>
                        Éditer</a>
                    <a href="#" @click.prevent="handlerDelete(d)">
                        <i class="icon-trash"></i>
                        Supprimer</a>
                </small>
            </h3>
            <div class="card-content">
                ...
            </div>
        </article>
        <hr>
        <button type="button" class="btn btn-primary" @click.prevent="handlerNew">
            <i class="icon-plus-circled"></i>
            Nouveau type de dépense
        </button>
    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  SpentGroupAdmin --filename.css SpentGroupAdmin.css --filename.js SpentGroupAdmin.js --dist public/js/oscar/dist public/js/oscar/src/SpentGroupAdmin.vue
    export default {
        props: {

        },
        data(){
            return {
                formData: null,
                error: null,
                deleteData: null,
                spentgroups: []
            }
        },

        methods:{
            handlerSubmit(){
                // let data = new FormData(), send;
                // data.append('label', this.formData.label);
                // if( this.formData.id ){
                //     data.append('id', this.formData.id);
                //     send = this.$http.post('?', data);
                //     send.then(ok => {
                //
                //         for( let i=0; i<this.disciplines.length; i++ ){
                //             if( this.disciplines[i].id == this.formData.id ){
                //                 this.disciplines[i].label = ok.body.discipline.label;
                //             }
                //         }
                //
                //     })
                // } else {
                //     send = this.$http.put('?', data);
                //     send.then(ok => {
                //         this.disciplines.push(ok.body.discipline);
                //         this.disciplines.sort( (a, b) => (a.label < b.label ? -1 : (a.label == b.label ? 0 : 1)));
                //     })
                // }
                //
                // send.catch(
                //     ko => {
                //         this.error = ko.body;
                //     }
                // ).finally( foo => {
                //     console.log('FINAL');
                //     this.formData = null;
                // });
            },

            handlerNew(){
                // this.formData = {
                //     id: null,
                //     label: "",
                //     original: null
                // };
            },

            handlerEdit( spentgroup ){
                // this.formData = {
                //     id: discipline.id,
                //     label: discipline.label,
                //     original: discipline.label
                // };
            },

            handlerCancelForm(){
                this.formData = null;
            },


            handlerDelete( spentgroup ){
                this.deleteData = spentgroup;
            },

            performDelete(){
                let spentgroup = this.deleteData;
                this.$http.delete('?id=' + spentgroup.id).then(
                    ok => {
                        let spentgroups = [];

                        this.spentgroups.forEach( item => {
                            if( item.id != spentgroup.id )
                                spentgroups.push(item);
                        });

                        this.spentgroups = spentgroups;
//                        this.deleteData = null;
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo => this.deleteData = null );
            }
        },
        mounted(){

        }
    }
</script>