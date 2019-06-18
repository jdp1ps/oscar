<template>
    <section style="position: relative">
        <h1>Type de dépense</h1>
        <pre>{{ masses }}</pre>
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

                    <label for="annexe">Annexe</label>
                    <small>Ce code est celui utilisé par les services financiers pour qualifier ce type de dépense</small>
                    <select name="annexe" id="annexe" v-model="formData.annexe" class="form-control">
                        <option value="">Ne pas utiliser</option>
                        <option :value="a" v-for="label, a in masses">{{ label }}</option>
                    </select>


                    <label for="form_description">Description</label>
                    <textarea type="text" class="form-control" v-model="formData.description" id="form_description" />
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
            <div class="overlay" v-if="confirm">
                <div class="alert alert-danger">
                    <h3>
                        {{ confirm }}
                        <a href="#" @click.prevent="error =null" class="float-right">
                            <i class="icon-cancel-outline"></i>
                        </a>
                    </h3>
                    <hr>
                    <nav>
                        <button type="submit" class="btn btn-success" @click.prevent="handlerConfirm">
                            <i class="icon-ok-circled"></i>
                            Valider
                        </button>
                        <button type="reset" class="btn btn-danger" @click.prevent="confirm = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </div>
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


        <transition name="fade">
            <div class="alert alert-info" v-if="mode == 'selection'">
                <p>Choisissez un élément à déplacer...</p>
            </div>
            <div class="alert alert-info" v-else-if="mode == 'destination'">
                <p>... choisissez l'emplacement où le déplacer.</p>
            </div>
        </transition>
        <div class="card-content spentarea">
            <spenttypeitem v-for="s in tree"
                       :spenttypegroup="s"
                           :annexes="masses"
                       :key="s.id"
                       :selection="selection"
                       :mode="mode"
                       @selection="handlerSelection($event)"
                       @destination="handlerDestination($event)"
                       @edit="handlerEdit($event)"
                       @blind="handlerBlind($event)"
                       @new="handlerNew(e, $event.id)"
                       @delete="handlerDelete($event)"/>
                <hr>

            <!--
                <button type="button" class="btn btn-primary" @click.prevent="handlerModeSelection">
                    <i class="icon-plus-circled"></i>
                    Réorganiser
                </button>

                <button type="button" class="btn btn-primary" @click.prevent="handlerNew">
                    <i class="icon-plus-circled"></i>
                    Nouveau type de dépense
                </button>

                <button type="button" class="btn btn-primary" @click.prevent="resetTree">
                    <i class="icon-plus-circled"></i>
                    Reset Tree
                </button>
                -->
            <button type="button" class="btn btn-primary" @click.prevent="loadPCG">
                <i class="icon-database"></i>
                Charger le <strong>Plan Comptable Général</strong>
            </button>
            </div>
    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  SpentGroupAdmin --filename.css SpentGroupAdmin.css --filename.js SpentGroupAdmin.js --dist public/js/oscar/dist public/js/oscar/src/SpentGroupAdmin.vue

    export default {
        props: {

        },
        data(){
            return {
                mode: 'default', // default | selection | destination
                selection: false,
                destination: null,
                formData: null,
                error: null,
                deleteData: null,
                spenttypegroups: [],
                confirm: "",
                waitdrop: false
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

                try {

                    this.spenttypegroups.forEach(item => {
                        let l = item.lft;
                        let r = item.rgt;
                        let lastParent = parents[parents.length - 1];

                        while (r > lastParent.rgt) {
                            parents[parents.length - 2].children.push(lastParent);
                            parents.pop();
                            lastParent = parents[parents.length - 1];
                        }

                        if (r > l + 1) {
                            item.children = [];
                            parents.push(item);
                        } else {
                            lastParent.children.push(item);
                        }
                    });

                    while (parents.length > 1) {
                        parents[parents.length - 2].children.push(parents.pop());
                    }
                } catch (e) {
                    this.error = "Liste des types de dépenses corrompu";
                }

                return parents[0].children;
            }
        },

        methods:{

            handlerConfirm(){
                let data = new FormData(), send;
                data.append('admin', "reset");
                this.$http.post('?', data).then(
                    ok => {
                        this.fetch();
                        this.formData = null;
                        this.confirm = "";
                    })
                    .catch( ko => {
                        this.error = ko.body;
                        this.confirm = "";
                    });
            },

            loadPCG(){
              this.confirm = "Remettre le plan comptable par défaut (plan comptable générale) ? ";
            },

            handlerModeSelection(){
                this.mode = "selection";
            },

            handlerSelection(selection){
                this.selection = selection;
                this.mode = 'destination';
            },

            handlerDestination(destination){
                this.move(this.selection, destination);
                this.selection = false;
                this.mode = 'default';
            },

            handlerModeMove(){
                this.mode = "move";
            },

            resetTree(){
                let data = new FormData(), send;
                data.append('admin', "reset");
                this.$http.post('?', data).then(ok => {
                        this.fetch();
                        this.formData = null;
                    });
            },

            move( moved, to){
                let data = new FormData();
                data.append('moved', moved.id);
                data.append('to', to.id);
                this.$http
                    .post('?', data)

                    .then( ok => {
                        this.fetch();
                        this.formData = null;
                    }, ko => {
                        this.error = ko.body;
                    });
            },

            performDelete(){
                let spentgroup = this.deleteData;
                this.$http.delete('?id=' + spentgroup.id).then(
                    ok => {
                        this.spenttypegroups = ok.data.spenttypegroups;
                    },
                    ko => {
                        this.error = ko.body;
                    }
                ).then( foo => this.deleteData = null );
            },



            fetch(){
                this.$http.get().then(
                    ok => {
                        this.spenttypegroups = ok.data.spenttypegroups;
                        this.masses = ok.data.masses;
                    },
                    ko => {
                        this.error = ko.body;
                    }
                );
            },

            handlerSubmit(){
                let data = new FormData(), send;

                data.append('label', this.formData.label);
                data.append('description', this.formData.description);
                data.append('code', this.formData.code);
                data.append('annexe', this.formData.annexe);
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

            handlerBlind(spentGroupType){
                let data = new FormData();

                data.append('action', "blind");
                data.append('id', spentGroupType.id);

                this.$http.post('?', data).then(ok => {
                        this.fetch();
                        this.formData = null;
                    });
            },

            handlerNew( evt, inside = 'root' ){
                this.formData = {
                    id: "",
                    label: "",
                    code: "",
                    annexe: "",
                    inside: inside,
                    description: ""
                };
            },


            //////////////////////////////////////////////////////////////////////////////////////////////// DRAG & DROP
            handlerDragItem( spenttypeitem ){
                this.moved = spenttypeitem;
            },

            handlerDropItem( spenttypeitem ){
                this.move(this.moved, spenttypeitem.id);
            },

            handlerDrop( spenttypeitem ){
                this.move(this.moved, 'root');
            },

            handlerDragEnter( spenttypeitem ){
                this.inside = true;
            },

            handlerDragLeave( spenttypeitem ){
                this.inside = false;
            },

            handlerEdit( spenttypegroup ){
                this.formData = {
                    id: spenttypegroup.id,
                    label: spenttypegroup.label,
                    annexe: spenttypegroup.annexe,
                    code: spenttypegroup.code,
                    description: spenttypegroup.description
                };
            },

            handlerCancelForm(){
                this.formData = null;
            },

            handlerDelete( spentgroup ){
                this.deleteData = spentgroup;
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>