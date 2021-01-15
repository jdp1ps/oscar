<template>
    <div class="form-group">

        <label class="sr-only" :for="name">Mot de passe {{ type }} / {{ value }}</label>
        <div class="input-group input-lg password-field">
            <div class="input-group-addon">
                <i class="glyphicon icon-lock"></i> <strong>{{ text }}</strong>
            </div>
            <input style="font-family: monospace" :name="name" type="text" v-model="value" class="form-control" placeholder="Mot de passe" :id="name" v-if="type == 'text'" />
            <input style="font-family: monospace" :name="name" type="password" v-model="value" class="form-control" placeholder="Mot de passe" :id="name" v-else />
            <div class="input-group-addon" @click="handlerShowPassword" style="cursor: pointer; background: white" title="Afficher le mot de passe pendant 5 secondes" :class="{'password-displayed': type == 'text'}">
                <i class="glyphicon icon-eye" v-if="type == 'text'"></i>
                <i class="glyphicon icon-eye-off" v-else></i>
            </div>
        </div>
    </div>
</template>
<script>
    let tempo = null;
    const TYPE_PASSWORD = "password";
    const TYPE_TEXT = "text";

    export default {
        props: {
            name: { required: true },
            value: { default: "" },
            text: { default: "foo" }
        },
        data(){
            return {
                displayPassword: false,
                type: TYPE_PASSWORD
            }
        },
        watch: {
            value(val){
                console.log(val);
                this.$emit('change', val);
            }
        },
        methods: {
            handlerShowPassword(){

                if( tempo === null ){
                    this.type = TYPE_TEXT;
                    tempo = new Promise( resolve => {
                        setTimeout( () => {
                            this.type = TYPE_PASSWORD;
                            tempo = null;
                        }, 5000);
                    })
                } else {
                    tempo = null;
                    this.type = TYPE_PASSWORD;
                }



            }
        }
    }
</script>