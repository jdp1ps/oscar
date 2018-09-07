<template>
    <div class="overlay" v-if="error">
        <div class="overlay-content">
            <i class="icon-cancel-outline overlay-closer" @click="error = null"></i>
            <h1><i class="icon-bug"></i> {{ errorTitle }}</h1>
            <p>La transmission des données à provoquée une erreur (Erreur <strong>{{ error.status }}</strong>), le serveur a répondu : </p>
            <pre>{{ errorMsg }}</pre>
        </div>
    </div>
</template>
<script>
    export default {
        computed: {
            errorTitle(){
                if( !this.error.status )
                    return "Erreur inattendue";

                switch(this.error.status){
                    case 400 : return "Données incorrectes"
                    case 401 : return "Accès non-autorisé"
                    case 404 : return "Cette ressource n'existe pas/plus"
                    case 500 : return "Erreur OSCAR"
                    case 501 : return "Fonctionnalité indisponible"
                    default :
                        return "Erreur"
                }
            },
            errorMsg(){
                if( !this.error.body )
                    return "Aucune précision donnée par le serveur";

                return this.error.body;
            }
        },

        props: {
            error: {
                default: null
            }
        }
    }
</script>