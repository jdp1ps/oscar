<template>
    <section style="position: relative; min-height: 100px">
        <ajax-oscar :oscar-remote-data="remoterState" />

        <article class="card xs" v-for="pole in poles">
          <h3>{{ pole }} - {{ i }}</h3>
        </article>
    </section>
</template>
<script>
    /******************************************************************************************************************/
    /* ! DEVELOPPEUR
    Depuis la racine OSCAR :

    cd front

    Pour compiler en temps réél :
    node node_modules/.bin/gulp administrationPcruPCWatch

    Pour compiler :
    node node_modules/.bin/gulp administrationPCPcru

     */
    import AjaxOscar from "./remote/AjaxOscar";
    import OscarRemoteData from "./remote/OscarRemoteData";
    import PasswordField from "./components/PasswordField";

    // test
    let oscarRemoteData = new OscarRemoteData();

    function flashMessage(){
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
                configuration: null,
                poles: ["foo", "bar"]
            }
        },

        methods:{
            handlerSuccess(success){
                let data = success.data;
                this.poles = data.poles;
            },

            fetch(){
                oscarRemoteData
                    .setDebug(true)
                    .setPendingMessage("Chargement du référenciel des pôles de compétitivité")
                    .setErrorMessage("Impossible de charger le référenciel")
                    .performGet(this.url, this.handlerSuccess);
            }
        },
        mounted(){
            this.fetch();
        }
    }
</script>