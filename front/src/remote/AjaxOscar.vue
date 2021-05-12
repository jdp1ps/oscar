<template>
    <div class="oscar-ajax"
         :class="{ 'has-error': oscarRemoteData.error != '', 'pending': oscarRemoteData.loading }"
         v-show="oscarRemoteData.loading || oscarRemoteData.error"
    >
        <div class="oscar-ajax-content">
            <div v-if="oscarRemoteData.loading" class="loading-message">
                <i class="icon-spinner animate-spin animate"></i> {{ oscarRemoteData.pendingMessage }}
            </div>
            <div v-if="oscarRemoteData.error" class="error-message">
                <span @click="oscarRemoteData.error = false" style="font-weight: bold; position: absolute; top: 5em; right: 5em ">x</span>
                <i class="icon-attention-1"></i> <strong>{{ oscarRemoteData.errorMessage }} </strong> :
                <code>{{ oscarRemoteData.error }}</code>
                <br>
                <a href="#" @click="oscarRemoteData.error = false" style="color: white">Fermer</a>
            </div>

        </div>
    </div>
</template>
<style scoped>

</style>
<script>
    export default {
        props: {
            oscarRemoteData: { required: true }
        }
    }
</script>