<template>
    <section>
        <input type="search" @keydown.enter="handlerSearch" v-model="search">
        <article v-for="person in persons">
            {{ person }}
        </article>
    </section>
</template>
<script>
    // poi watch --format umd --moduleName  PersonsList --filename.css PersonsList.css --filename.js PersonsList.js --dist public/js/oscar/dist public/js/oscar/src/PersonsList.vue
    export default {
        data(){
            return {
                search: "",
                persons: []
            }
        }
    }
</script>