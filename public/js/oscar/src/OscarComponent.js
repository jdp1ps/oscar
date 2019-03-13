<template>
    <div>
        <div class="overlay">
            <div class="overlay-content" style="">
                {{ loadingMsg }}
                <div class="overlay-closer" @click="loading = false">X</div>
            </div>
        </div>
    </div>
</template>
<script>

    // nodejs node_modules/.bin/poi watch --format umd --moduleName  ActivityPersons --filename.js ActivityPersons.js --dist public/js/oscar/dist public/js/oscar/src/ActivityPersons.vue

    export default {
        props: {
            url: { required: true }
        },

        data(){
            return {
                error: "",
                loading: ""
            }
        },

        methods:{
            fetch(onOk){
                this.loading = this.label +" : chargement...";
                this.$http.get(this.url).then(
                    ok => {
                        console.log("OK", ok);
                        onOk(ok);
                    },
                    ko => {
                        console.log("ERROR", ko);
                        this.error = ko.body;
                    }
                ).then(foo => {
                    console.log("THEN", foo);

                   this.loading = "";
                });
            }
        },
    }
</script>