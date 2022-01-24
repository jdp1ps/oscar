<template>
    <section>
        <transition name="fade">
            <div class="vue-loader" v-if="errors.length">
                <div class="alert alert-danger" v-for="error, i in errors">
                    {{ error }}
                    <a href="" @click.prevent="errors.splice(i,1)"><i class="icon-cancel-outline"></i></a>
                </div>
            </div>
        </transition>

        <div class="vue-loader-component" v-if="loading">
            <span>Chargement</span>
        </div>



        <nav class="buttons">
            <a href="" class="btn btn-primary" @click.prevent="handlerWorkPackageNew" v-if="editable">Nouveau lot</a>
        </nav>

        <section class="workpackages">
            <workpackage v-for="wp in workpackages"
                         v-bind:key="wp.id"
                         :workpackage="wp"
                         :persons="persons"
                         :editable="editable"
                         :is-validateur="isValidateur"
                         @addperson="addperson"
                         @workpackageupdate="handlerWorkPackageUpdate"
                         @workpackagepersonupdate="handlerUpdateWorkPackagePerson"
                         @workpackagepersondelete="handlerWorkPackagePersonDelete"
                         @workpackagedelete="handlerWorkPackageDelete"
                         @workpackagecancelnew="handlerWorkPackageCancelNew"
            ></workpackage>
        </section>
    </section>
</template>
<script>
import Workpackage from "./components/Workpackage";

export default {
    components: {
        'workpackage': Workpackage
    },

    data(){
        return {
            loading: false,
            errors: [],
            workpackages: [],
            persons: [],
            editable: false,
            isDeclarant: false,
            isValidateur: false,
            token: 'DEFAULT_TKN'
        }
    },

    props: {
        url: { required: true }, //'<?= $this->url('workpackage/rest', ['idactivity' => $entity->getId()]) ?>',
        token: { required: true }, // '<?= $tokenValue ?>',
        isValidateur:  { required: true }, //,
        editable:  { required: true }, //
        Bootbox: { required: true }
    },

    watch: {
    },
    computed: {
    },

    created () {
        this.fetch();
    },

    methods: {
        ////////////////////////////////////////////////////////////////////////
        // HANDLER
        handlerWorkPackageCancelNew(workpackage){
            this.workpackages.splice(this.workpackages.indexOf(workpackage), 1);
        },

        handlerWorkPackageNew(){
            this.workpackages.push({
                id: -1,
                code: "Nouveau Lot",
                label : "",
                persons: [],
                description: ""
            })
        },

        handlerWorkPackagePersonDelete(workpackageperson){
            this.Bootbox.confirm("Supprimer le déclarant ? ", (result) => {
                if( result ){
                    this.$http.delete(this.url+"?workpackagepersonid=" + workpackageperson.id).then(
                        (res) => {
                            this.fetch();
                        },
                        (err) => {
                            this.errors.push("Impossible de supprimer le déclarant : " + err.body);
                        }
                    );
                }
            });
        },

        handlerWorkPackageDelete(workpackage){
            this.Bootbox.confirm("Souhaitez-vous supprimer ce lot ?", (result) => {
                if( result ) {
                    this.$http.delete(this.url+"?workpackageid=" + workpackage.id).then(
                        (res) => {
                            this.fetch();
                        },
                        (err) => {
                            this.errors.push("Impossible de supprimer le lot : " + err.body);
                        }
                    );
                }
            });
        },

        handlerWorkPackageUpdate(workPackageData){
            var datas = new FormData();
            for( var key in workPackageData ){
                datas.append(key, workPackageData[key]);
            }
            if( workPackageData.id > 0 ){
                console.log("MAJ du LOT");
                // Mise à jour
                datas.append('workpackageid', workPackageData.id);
                this.$http.post(this.url, datas).then(
                    (res) => {
                        this.fetch();
                    },
                    (err) => {
                        this.errors.push("Impossible de mettre à jour le lot de travail : " + err.body);
                    }
                );
            } else {
                console.log("NOUVEAU du LOT");
                datas.append('workpackageid', -1);
                this.$http.put(this.url, datas).then(
                    (res) => {
                        this.fetch();
                    },
                    (err) => {
                        this.errors.push("Impossible de créer le lot de travail : " + err.body);
                    }
                ).then(foo=> this.fetch());
            }
        },

        handlerUpdateWorkPackagePerson(workpackageperson, duration){
            var datas = new FormData();
            datas.append('workpackagepersonid', workpackageperson.id);
            datas.append('duration', duration);
            this.$http.post(this.url, datas).then(
                (res) => {
                    workpackageperson.duration = duration;
                },
                (err) => {
                    this.errors.push("Impossible de mettre à jour les heures prévues : " + err.body);
                }
            );
        },

        addperson(personid, workpackageid){
            console.log(arguments);
            var data = new FormData();
            data.append('idworkpackage', workpackageid);
            data.append('idperson', personid);

            this.$http.put(this.url, data).then(
                (res) => {
                    this.fetch();
                },
                (err) => {
                    this.errors.push("Impossible d'ajouter le déclarant : " + err.body);
                }
            ).then(()=> this.loading = false );
        },

        fetch(){
            this.loading = true;
            console.log(this.url);
            this.$http.get(this.url).then(
                (res) => {
                    this.workpackages = res.body.workpackages;
                    this.persons = res.body.persons;
                    this.editable = res.body.editable;
                    this.isDeclarant = res.body.isDeclarant;
                    this.isValidateur = res.body.isValidateur;
                },
                (err) => {
                    this.errors.push("Impossible de charger les lots de travail : " + err.body);
                }
            ).then(()=> this.loading = false );

        }
    }
}
</script>