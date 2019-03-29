
    export default {
        props: {
            url: { required: true },
            urlPost: { required: true },
            urlDelete: { required: true },
            urlEdit: {required: true }
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
    };
