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
<style scoped lang="scss">
    $colorSelected: #0088cc;

    .jck-selector {
        border: solid #aaa thin;
        background: white;
        line-height: 1.5em;
        position: relative;
    }
    .jck-selector:hover .list{
        display: block;
    }
    .jck-selector .list {
        display: none;
        position: absolute;
        background: white;
        z-index: 2;
        box-shadow: 0 .5em .3em rgba(0,0,0,.3em);
    }
    .jck-selector .item {
        transition: all .25s;
        cursor: pointer;
        color: #999999;
        border-bottom: solid thin #CCC;
    }
    .jck-selector .item:last-child {
        border-bottom: none;
    }
    .jck-selector .item small {
        color: rgba(0,0,0,.5);
        font-weight: 100;
    }
    .jck-selector .item.selected {
        background: lighten($colorSelected, 40%);
        color: black;
        font-weight: bold;
    }
    .jck-selector .item:hover {
        background: $colorSelected;
        color: white;
    }

    .jck-selector .item .icon-check {
        display: none;
    }
    .jck-selector .item.selected .icon-check { display: inline-block }
    .jck-selector .item.selected .icon-check-empty { display: none }

</style>
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