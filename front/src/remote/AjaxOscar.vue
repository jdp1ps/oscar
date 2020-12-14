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
                <span @click="oscarRemoteData.error = false">CLOSE</span>
                <i class="icon-attention-1"></i> <strong>{{ oscarRemoteData.errorMessage }} </strong> :
                <i>{{ oscarRemoteData.error }}</i>
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