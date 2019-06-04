<template>
    <section style="position: relative">
        <h1>Type de dépense</h1>

        <transition name="fade">
            <div class="overlay" v-if="formData">
                <form action="" @submit.prevent="handlerSubmit">
                    <h1 v-if="formData.original">Modifier {{ formData.original }}</h1>
                    <h1 v-else="formData.original">Nouveau type de dépense</h1>
                    <label for="form_label">Intitulé</label>
                    <input type="text" class="form-control lg" v-model="formData.label" id="form_label" />

                    <label for="form_code">Code</label>
                    <small>Ce code est celui utilisé par les services financiers pour qualifier ce type de dépense</small>
                    <input type="text" class="form-control" v-model="formData.code" id="form_code" />

                    <label for="form_description">Description</label>
                    <textarea type="text" class="form-control" v-model="formData.description" id="form_description" />
                    <hr>
                    <pre>{{ formData }}</pre>
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
                    <h3>Supprimer le type <strong>{{ deleteData.label }}</strong> ?</h3>
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
        <div @mousemove="handlerMouseMove($event)">
        <spenttypeitem v-for="s in tree"
                       :spenttypegroup="s"
                       :key="s.id"
                       :waitdrop="waitdrop"
                       @edit="handlerEdit($event)"
                       @drag="handlerDrag($event)"
                       @stopdrag="handlerStopDrag($event)"
                       @new="handlerNew(e, $event.id)"
                       @delete="handlerDelete($event)"/>
        </div>

        <!--<article v-for="d in tree" class="card">-->
            <!--<h3 class="card-title">-->
                <!--<span>-->
                    <!--[{{d.id}}]-->
                    <!--{{ d.label }}-->
                <!--</span>-->
                <!--<p class="small">{{ d.description }}</p>-->
                <!--<small>-->
                    <!--<a href="#" @click.prevent="handlerEdit(d)">-->
                        <!--<i class="icon-pencil"></i>-->
                        <!--Éditer</a>-->
                    <!--<a href="#" @click.prevent="handlerDelete(d)">-->
                        <!--<i class="icon-trash"></i>-->
                        <!--Supprimer</a>-->
                <!--</small>-->
            <!--</h3>-->
            <!--<div class="card-content">-->
                <!--<pre>{{ d }}</pre>-->
                <!--<button type="button" class="btn btn-primary btn-xs" @click.prevent="handlerNew($event, d.id)">-->
                    <!--<i class="icon-plus-circled"></i>-->
                    <!--Nouveau type de dépense-->
                <!--</button>-->
            <!--</div>-->
        <!--</article>-->
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
                spenttypegroups: [],
                waitdrop: false,
                dragged: null
            }
        },

        computed: {
            tree(){

                let dest = {
                    lft: 0,
                    rgt: this.spenttypegroups.length * 2 + 1,
                    children: []
                };

                let parents = [dest];


                this.spenttypegroups.forEach( item => {
                    let l = item.lft;
                    let r = item.rgt;
                    let lastParent = parents[parents.length-1];

                    while (r > lastParent.rgt) {
                        parents[parents.length-2].children.push(lastParent);
                        parents.pop();
                        lastParent = parents[parents.length-1];
                    }

                    if( r > l+1 ){
                        item.children = [];
                        parents.push(item);
                    } else {
                        lastParent.children.push(item);
                    }
                });

                return parents[0].children;
            }
        },

        methods:{


            handlerSubmit(){
                let data = new FormData(), send;

                data.append('label', this.formData.label);
                data.append('description', this.formData.description);
                data.append('code', this.formData.code);
                data.append('inside', this.formData.inside);

                if( this.formData.id ){
                    data.append('id', this.formData.id);
                    send = this.$http.post('?', data);
                    send.then(ok => {
                        this.fetch();
                        this.formData = null;
                    })
                } else {
                    send = this.$http.put('?', data);
                    send.then(ok => {
                        this.fetch();
                        this.formData = null;
                    })
                }

                send.catch(
                    ko => {
                        this.error = ko.body;
                    }
                );
            },

            handlerNew( evt, inside = 'root' ){
                this.formData = {
                    id: "",
                    label: "",
                    code: "",
                    inside: inside,
                    description: ""
                };
            },
            handlerDrag( evt ){
                console.log("ça drag !");
                this.dragged = evt;
                this.waitdrop = true;
            },

            handlerMouseMove(e){
                if( this.waitdrop ){
                    if( this.dragged ){
                        // console.log(e.movementY, e);
                        let posY = parseInt(this.dragged.style.top) + e.movementY;
                        console.log(this.dragged.offsetTop);
                        this.dragged.style.top = (this.dragged.offsetTop + e.movementY) +'px';
                    }
                }
            },
            handlerStopDrag( evt ){
                console.log("ça drag plus !");
                this.waitdrop = false;
            },

            handlerEdit( spenttypegroup ){
                this.formData = {
                    id: spenttypegroup.id,
                    label: spenttypegroup.label,
                    code: spenttypegroup.code,
                    description: spenttypegroup.description
                };
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
                        console.log(ok);
                    },
                    ko => {
                        console.log(ko);
                        this.error = ko.body;
                    }
                ).then( foo => this.deleteData = null );
            },

            fetch(){
                this.$http.get().then(
                    ok => {
                        console.log(ok);
                        this.spenttypegroups = ok.data.spenttypegroups;
                    },
                    ko => {
                        console.log(ko);
                    }
                );
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>