<template>
    <div style="position: relative">

        <div class="dropdown " style="position: absolute; right: 0">
            <button class="btn-xs btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <i class="icon-plus-circled"></i>
                Ajouter un numéro
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                <li v-for="k in keys" :class="{ 'disabled': values.hasOwnProperty(k)  }"><a href="#" @click.prevent="handlerAddNum(k)">{{ k }}</a></li>
                <template v-if="editable">
                    <li role="separator" class="divider"></li>
                    <li><a href="#" @click.prevent="handleNewNum">Nouveau type de numéro</a></li>
                </template>
            </ul>
        </div>
        <hr style="margin-bottom: 2em">
        <div class="card" v-for="v,k in values">
            <strong>{{ k }}</strong>
            <input type="text" :name="name+'[' + k +']'" v-model="values[k]" />
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
                values: {}
            }
        },

        computed: {

        },

        methods:{
            handlerAddNum(key){
                console.log("Ajout de ", key);
                if( !this.values.hasOwnProperty(key) ){
                    let keys = JSON.parse(JSON.stringify(this.values));
                    keys[key] = "";
                    this.values = keys;
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
                Object.keys(this.values).forEach( k => {
                    if( k != key )
                        newOne[k] = this.values[k];
                })
                this.values = newOne;
            }
        },

        mounted(){
            console.log("Mounted", this.value);
            if( this.value && this.value.length != 0 )
               this.values = JSON.parse(JSON.stringify(this.value));
        }

    }
</script>