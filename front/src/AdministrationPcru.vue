<template>
    <section style="position: relative; min-height: 100px">
        <ajax-oscar :oscar-remote-data="remoterState" />

        <div v-if="configuration">
            <div class="material-switch">
                <input id="pcru_enabled" name="parameter_value" type="checkbox" v-model="configuration.pcru_enabled" />
                <label for="pcru_enabled" class="label-primary">Module PCRU {{configuration}} ?</label>
            </div>
        </div>

    </section>
</template>
<script>
    /******************************************************************************************************************/
    /* ! DEVELOPPEUR
    Depuis la racine OSCAR :

    cd front

    Pour compiler en temps réél :
    node node_modules/.bin/gulp administrationPcru

    Pour compiler :
    node node_modules/.bin/gulp administrationPcru

     */

    import AjaxOscar from "./remote/AjaxOscar";
    import OscarRemoteData from "./remote/OscarRemoteData";

    // test
    let oscarRemoteData = new OscarRemoteData();

    function flashMessage(){
        // TODO
    }

    export default {

        components: {
            "ajax-oscar": AjaxOscar
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