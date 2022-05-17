<template>
    <div class="jck-selector">
        <div class="selected">
            <i class="icon-cog"></i>
            <strong>{{ selectedLabel }}</strong>
        </div>
        <div class="list">
            <div class="item" v-for="opt in choose" :class="{ 'selected': (selected.indexOf(opt.id) > -1) }" @click="toggleSelection(opt.id)">
                <i class="icon-check"></i>
                <i class="icon-check-empty"></i>
                {{ opt.label }}
                <small> ({{ opt.description }})</small>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            choose: {
                default: null
            },
            selected: {
                default: null
            }
        },

        computed: {
            selectedLabel(){
                if( this.selected.length ){
                    let label = [];
                    this.choose.forEach(item => {
                       if(this.selected.indexOf(item.id) > -1){
                           label.push(item.label);
                       }
                    });
                    return label.join(', ');
                }
                return "Aucune selection";
            }
        },

        methods: {
            toggleSelection(value){
                let selection = [];
                if( this.selected )
                    selection = this.selected;

                let pos = selection.indexOf(value)
                if( pos > -1 ){
                    selection.splice(pos, 1);
                } else {
                    selection.push(value);
                }
                console.log(selection);
                this.$emit('change', selection);
            }
        }
    }
</script>