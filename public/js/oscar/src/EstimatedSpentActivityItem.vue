<template>
    <section v-show="!type.empty">
        <div class="line" :class="'sizing-' + level">
            <div class="intitule">
                <i class="icon-angle-down" @click.prevent="open = false" v-if="open"></i>
                <i class="icon-angle-right" @click.prevent="open = true" v-else></i>
                <strong class="code">{{ type.code }}</strong>
                {{ type.label }}
                <span v-if="type.annexe">ANNEXE {{ type.annexe }}</span>

            </div>
            <div v-for="year in years" class="year">
                <input type="text" class="input-sm form-control" placeholder="0.0" v-if="type.annexe" v-model="values[type.id][year]" @change="handlerChangeValue(year, $event)">
            </div>
            <div class="total">t {{ total }}</div>
        </div>


        <estimatedspentactivityitem :filter="filter" :type="t" :years="years" :values="values" @changevalue="$emit('changevalue', $event)"
                                    v-for="t in type.children" :key="t.id" :level="level+1" v-show="open && !t.blink && !t.empty && t"/>

    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  EstimatedSpentActivityItem --filename.css EstimatedSpentActivityItem.css --filename.js EstimatedSpentActivityItem.js --dist public/js/oscar/dist public/js/oscar/src/EstimatedSpentActivityItem.vue

    export default {
        props: {
            type: { required: true },
            years: { required: true },
            values: { required: true },
            level: { default: 1 },
            filter: { default: "" }
        },

        mounted(){

        },

        data(){
            return {
                open: true
            }
        },



        computed: {

            total(){
                let t = 0.0;
                if( !this.values || !this.values[this.type.id] ){
                    return "~";
                }
                Object.keys(this.values[this.type.id]).forEach( k => {
                   t += this.values[this.type.id][k];
                });
                return "test" + t;
            }
            // filtered(){
            //         console.log('filtered');
            //     if( this.filter ){
            //         let out = [];
            //         this.type.children.forEach( item => {
            //             if( item.corpus.indexOf(this.filter) > -1){
            //                 out.push(item);
            //             }
            //         })
            //         return out;
            //     } else {
            //         return this.type.children;
            //     }
            // }
        },

        methods:{
            handlerChangeValue(year, evt){
                this.$emit('changevalue', {
                    year: year,
                    id: this.type.id,
                    value: evt.target.value

                })
            }
        }
    }
</script>