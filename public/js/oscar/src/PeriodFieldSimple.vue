<template>
    <div class="row">
        <div class="col-md-4">
            <select v-model="activeMode" class="form-control" @change="handlerFilterPeriod">
                <option v-for="label,key in modes" :value="key">{{label}}</option>
            </select>
        </div>
        <div class="col-md-8">
            <span v-show="activeMode != 'none'">
                <span v-if="activeMode == 'interval'">Du</span>
                <span v-else>Période : </span>
                <input type="hidden" name="periodStart" :value="periodStart" />
                <periodselector @change="periodStart = $event" :period="periodStart" v-show="activeMode != 'none'"/>
            </span>
            <span v-show="activeMode == 'interval'">
                au
                <input type="hidden" name="periodEnd" :value="periodEnd" />
                <periodselector @change="periodEnd = $event" :period="periodEnd" v-show="activeMode == 'interval'"/>
            </span>
        </div>
    </div>
</template>
<script>
    import PeriodSelector from './PeriodSelector.vue';

    //node node_modules/.bin/poi watch --format umd --moduleName  PeriodFieldSimple --filename.js PeriodFieldSimple.js --dist public/js/oscar/dist public/js/oscar/src/PeriodFieldSimple.vue
    export default {
        props: {
            queryPeriodStart: { require: true },
            queryPeriodEnd: { require: true }
        },

        components: {
            periodselector: PeriodSelector
        },

        data() {
            return {
                search: null,
                periodStart: "",
                periodEnd: "",
                modes: {
                    "none": "Aucun filtre...",
                    "fix": "Période donnée",
                    "interval": "D'une période à l'autre"
                },
                activeMode: "none",
                tmpvalues: {
                    periodStart: null,
                    periodEnd: null
                }
            }
        },
        methods: {
            handlerPeriodSelected(e){
                this.periodStart = e;
            },
            handlerFilterPeriod(e){

                if( this.periodStart ){
                    this.tmpvalues.periodStart = this.periodStart;
                }
                if( this.periodEnd ){
                    this.tmpvalues.periodEnd = this.periodEnd;
                }

                // On mémorise les valeurs
                switch (this.activeMode) {
                    case 'none':
                        this.periodStart = '';
                        this.periodEnd = '';
                        break;

                    case 'fix' :
                        this.periodStart = this.tmpvalues.periodStart;
                        this.periodEnd = '';
                        break;

                    case 'interval' :
                        this.periodStart = this.tmpvalues.periodStart;
                        this.periodEnd = this.tmpvalues.periodEnd;
                        break;
                }
            }
        },

        mounted(){
            if( this.queryPeriodStart ){
                this.periodStart = this.queryPeriodStart;
                this.activeMode = "fix";
            }
            if( this.queryPeriodEnd ){
                this.periodEnd = this.queryPeriodEnd;
                this.activeMode = "interval";
            }
        }
    }
</script>
