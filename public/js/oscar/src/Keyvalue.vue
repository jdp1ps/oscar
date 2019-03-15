<template>
    <div style="position: relative">

        <div class="dropdown " style="position: absolute; right: 0">
            <button class="btn-xs btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <i class="icon-plus-circled"></i>
                Ajouter un numéro
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                <li v-for="k in keys" :class="{ 'disabled': value.hasOwnProperty(k)  }"><a href="#" @click.prevent="handlerAddNum(k)">{{ k }}</a></li>
                <template v-if="editable">
                    <li role="separator" class="divider"></li>
                    <li><a href="#" @click.prevent="handleNewNum">Nouveau type de numéro</a></li>
                </template>
            </ul>
        </div>
        <hr style="margin-bottom: 2em">
        <div class="card" v-for="v,k in value">
            <strong>{{ k }}</strong>
            <input type="text" :name="name+'[' + k +']'" :value="v" />
            <i class="icon-trash" @click="remove(k)"></i>
        </div>
    </div>
</template>
<script>

    // nodejs node_modules/.bin/poi watch --format umd --moduleName  Keyvalue --filename.js Keyvalue.js --dist public/js/oscar/dist public/js/oscar/src/Keyvalue.vue

    export default {
        props: {
            name: { required: true },
            keys: { required: true },
            value: { required: true },
            editable: { default: false }
        },

        data(){
            return {

            }
        },

        computed: {

        },

        methods:{
            handlerAddNum(key){
                if( !this.value.hasOwnProperty(key) ){
                    let keys = JSON.parse(JSON.stringify(this.value));
                    keys[key] = "";
                    this.value = keys;
                }
            },
            handleNewNum(){
                let prompt = window.prompt('Type de numéro ?');
                if( prompt ){
                    this.handlerAddNum(prompt);
                }
            },
            remove(key){
                let newOne = {};
                Object.keys(this.value).forEach( k => {
                    if( k != key )
                        newOne[k] = this.value[k];
                })
                this.value = newOne;
            }
        },

    }
</script>