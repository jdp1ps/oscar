<template>
    <section style="position: relative; min-height: 100px">
        <ajax-oscar :oscar-remote-data="remoterState" />

        <div v-if="configuration" class="container" >
            <form action="" method="post">
            <div class="row">
                <div class="col-md-3">
                    Module <strong>{{ configuration.pcru_enabled ? 'Actif' : 'Inactif' }}</strong>
                </div>
                <div class="col-md-9">
                    <div class="material-switch">
                        <input id="pcru_enabled" name="pcru_enabled" type="checkbox" v-model="configuration.pcru_enabled" />
                        <label for="pcru_enabled" class="label-primary"></label>
                    </div>
                </div>
            </div>

            <section :class="configuration.pcru_enabled ? 'enabled' : 'disabled'">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Accès FTP</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-5 col-md-push-1">
                            <div class="form-group">
                                <label class="sr-only" for="host"></label>
                                <div class="input-group input-lg">
                                    <div class="input-group-addon">
                                        <i class="glyphicon icon-building"></i> <strong>Hôte</strong>
                                    </div>
                                    <input type="text" id="host" name="host" v-model="configuration.pcru_host" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 col-md-push-1">
                            <div class="form-group">
                                <label class="sr-only" for="port"></label>
                                <div class="input-group input-lg">
                                    <div class="input-group-addon">
                                        <i class="glyphicon icon-logout"></i> <strong>Port</strong>
                                    </div>
                                    <input type="text" id="port" name="port" v-model="configuration.pcru_port" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 col-md-push-1">
                            <div class="form-group">
                                <label class="sr-only" for="user"></label>
                                <div class="input-group input-lg">
                                    <div class="input-group-addon">
                                        <i class="glyphicon icon-user"></i> <strong>Identifiant</strong>
                                    </div>
                                    <input type="text" id="user" name="user" v-model="configuration.pcru_user" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 col-md-push-1">
                            <password-field :value="configuration.pcru_pass"
                                            :name="'pass'"
                                            :text="'Mot de passe'"
                                            @change="configuration.pcru_pass = $event"/>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 col-md-push-1">
                            <div class="form-group">
                            <label class="sr-only" for="ssh"></label>
                            <div class="input-group input-lg">
                                <div class="input-group-addon">
                                    <i class="glyphicon icon-plug"></i> <strong>Clef SSH</strong>
                                </div>
                                <input type="text" id="ssh" name="ssh" v-model="configuration.pcru_ssh" class="form-control" />
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="row">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-floppy"></i>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </section>
            </form>
        </div>
    </section>
</template>
<script>
    /******************************************************************************************************************/
    /* ! DEVELOPPEUR
    Depuis la racine OSCAR :

    cd front

    Pour compiler en temps réél :
    node node_modules/.bin/gulp administrationPcruWatch

    Pour compiler :
    node node_modules/.bin/gulp administrationPcru

     */

    import AjaxOscar from "./remote/AjaxOscar";
    import OscarRemoteData from "./remote/OscarRemoteData";
    import PasswordField from "./components/PasswordField";

    // test
    let oscarRemoteData = new OscarRemoteData();

    function flashMessage(){
        // TODO
    }

    export default {

        components: {
            "ajax-oscar": AjaxOscar,
            "password-field": PasswordField
        },

        props: {
            url: { required: true }
        },

        data(){
            return {
                formData: null,
                remoterState: oscarRemoteData.state,
                configuration: null
            }
        },

        methods:{


            // performEdit(){
            //     let documentId = this.editData.document.id;
            //     let newType = this.editData.documentype_id;
            //     this.editData = null;
            //     let formData = new FormData();
            //     formData.append('documentId', documentId);
            //     formData.append('type', newType);
            //     oscarRemoteData
            //         .setPendingMessage("Modification du type de document")
            //         .setErrorMessage("Impossible de modifier le type de document")
            //         .performPost(this.urlDocumentType, formData, (response) => {
            //             this.fetch();
            //         });
            // },

            handlerSuccess(success){
                let data = success.data;
                console.log("SUCCESS", success);
                this.configuration = data.configuration_pcru;
            },

            fetch(){
                oscarRemoteData
                    .setDebug(true)
                    .setPendingMessage("Chargement de la configuration PCRU")
                    .setErrorMessage("Impossible de charger la configuration")
                    .performGet(this.url, this.handlerSuccess);
            }
        },

        mounted(){
            this.fetch();
        }

    }
</script>