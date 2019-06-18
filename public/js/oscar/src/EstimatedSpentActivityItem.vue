<template>
    <section>
        <div class="line" :class="'sizing-' + level">
            <div class="intitule">
                <i class="icon-angle-down" @click.prevent="open = false" v-if="open"></i>
                <i class="icon-angle-right" @click.prevent="open = true" v-else></i>
                <strong class="code">{{ type.code }}</strong>
                {{ type.label }}<br>

            </div>
            <div v-for="year in years" class="year">
                <input type="text" class="input-sm form-control" placeholder="0.0">
            </div>
            <div class="total">t {{ level }}</div>
        </div>

        <estimatedspentactivityitem :filter="filter" :type="t" :years="years" v-for="t in type.children" :key="t.id" :level="level+1" v-show="open && !t.blink"/>

    </section>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  EstimatedSpentActivityItem --filename.css EstimatedSpentActivityItem.css --filename.js EstimatedSpentActivityItem.js --dist public/js/oscar/dist public/js/oscar/src/EstimatedSpentActivityItem.vue

    export default {
        props: {
            type: { required: true },
            years: { required: true },
            level: { default: 1 },
            filter: { default: "" }
        },

        data(){
            return {
                open: true
            }
        },

        computed: {
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

        }
    }
</script>