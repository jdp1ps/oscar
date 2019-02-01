<template>
    <h2>Référent</h2>
</template>

<script>
    // poi watch --format umd --moduleName  PersonReferent --filename.css timesheetmonth.css --filename.js PersonReferent.js --dist public/js/oscar/dist public/js/oscar/src/PersonReferent.vue
    export default {
        name: "PersonReferent",

        methods: {
            fetch(){
                this.$http.get('').then(
                    ok => {
                        console.log(ko);
                    },
                    ko => {
                        console.error(ko);
                    }
                )
            }
        },

        mounted(){
            this.fetch();
        }
    }
</script>