<template>
    <section>
        <h1>TVA</h1>

        <div class="alert alert-info" v-show="loading">{{ loading }}</div>

        <transition name="fade">
            <div class="overlay" v-if="formData">
                <form action="" @submit.prevent="handlerSubmit">
                    <h1 v-if="formData.original">Modifier {{ formData.original }}</h1>
                    <h1 v-else="formData.original">Nouvelle discipline</h1>

                    <div>
                        <label for="form_label">Intitulé</label>
                        <input type="text" class="form-control lg" v-model="formData.label" id="form_label" />
                    </div>

                    <div>
                        <label for="form_rate">Intitulé</label>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model="formData.rate" id="form_rate" />
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" v-model="formData.active"> Active
                        </label>
                    </div>

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
                    <h3>Supprimer la TVA <strong>{{ deleteData.label }}</strong> ?</h3>
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

        <article v-for="t in tvas" class="card" :class="t.active ? 'active': 'disabled'">
            <h3 class="card-title">
                <span>
                    <span class="label" :class="t.active ? 'label-info' : 'label-default'">
                        <i v-if="t.active" class="icon-ok-circled"></i>
                        <i v-else class="icon-minus-circled"></i>
                        {{ t.rate }} %
                    </span>
                    <span class="intitule">
                        {{ t.label }}
                    </span>
                    <span class="label xs" :class="t.used > 0 ? 'label-primary' : 'label-default'" title="Nombre d'utilisation dans les activités">
                        <i class="icon-cube"></i>
                        {{ t.used }}
                    </span>
                </span>
                <small class="right">
                    <a href="#" @click.prevent="handlerEdit(t)">
                        <i class="icon-pencil"></i>
                        Éditer</a>
                    <a href="#" @click.prevent="handlerDelete(t)">
                        <i class="icon-trash"></i>
                        Supprimer</a>
                </small>
            </h3>
        </article>
        <hr>
        <button type="button" class="btn btn-primary" @click.prevent="handlerNew">
            <i class="icon-plus-circled"></i>
            Nouvelle TVA
        </button>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  Tva --filename.css Tva.css --filename.js Tva.js --dist public/js/oscar/dist public/js/oscar/src/Tva.vue

    export default {
        data(){
            return {
                formData: null,
                loading: "",
                tvas: null,
                error: null,
                deleteData: null
            }
        },

        computed:{
            disciplines(){
                return this.disc;
            }
        },

        methods:{
            fetch(){
                this.loading = "Chargement des TVAS";
                this.$http.get('?').then(
                    ok => {
                        this.tvas = ok.body.tvas;
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo =>{
                    this.loading = null;
                });
            },

            handlerSubmit(){
                let data = new FormData();

                data.append('label', this.formData.label);
                data.append('rate', this.formData.rate.toString().replace(/,/g,'.'));
                data.append('active', this.formData.active);
                data.append('id', this.formData.id);

                this.$http.post('?', data).then(
                    ok => {
                      this.fetch();
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).finally( foo => {
                    this.formData = null;
                });
            },

            handlerNew(){
                this.formData = {
                    id: null,
                    label: "Intitulé",
                    rate: 0.0,
                    active: false,
                    original: null
                };
            },

            handlerEdit( tva ){
                this.formData = {
                    id: tva.id,
                    label: tva.label,
                    active: tva.active,
                    rate: tva.rate,
                    original: tva.label
                };
            },

            handlerCancelForm(){
                this.formData = null;
            },


            handlerDelete( discipline ){
                this.deleteData = discipline;
            },

            performDelete(){
                let tva = this.deleteData;
                this.$http.delete('?id=' + tva.id).then(
                    ok => {
                        this.fetch();
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo => this.deleteData = null );
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>