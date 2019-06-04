<template>

        <article class="card spenttype" :class="{ 'dragged': dragged }" @mousedown.stop="startDrag($event)" @mouseup="stopDrag" @mouseleave="stopDrag">
            <h3 class="card-title">
                <span>
                    [{{ spenttypegroup.id}} ]
                    {{ spenttypegroup.label }}
                    --- {{ waitdrop }}
                </span>
                <p class="small">{{ spenttypegroup.description }}</p>
                <small>
                    <a href="#" @click.prevent="$emit('edit', spenttypegroup)">
                        <i class="icon-pencil"></i>
                        Éditer</a>
                    <a href="#" @click.prevent="$emit('delete', spenttypegroup)">
                        <i class="icon-trash"></i>
                        Supprimer</a>
                </small>
            </h3>
            <div class="card-content" @mouseover="inside = true" @mouseleave="inside = false" :class="{ 'inside' : waitdrop && inside }">
                <spenttypeitem v-for="s in spenttypegroup.children"
                               :spenttypegroup="s"
                               :waitdrop="waitdrop"
                               :key="s.id"
                               @edit="$emit('edit', $event)"
                               @drag="$emit('drag', $event)"
                               @stopdrag="$emit('stopdrag', $event)"
                               @delete="$emit('delete', $event)"
                               @new="$emit('new', $event)"
                />
                <button type="button" class="btn btn-primary btn-xs" @click.prevent="$emit('new', spenttypegroup)">
                    <i class="icon-plus-circled"></i>
                    Nouveau type de dépense
                </button>
            </div>
        </article>

</template>
<style scoped>
    .dragged {
        background: #0b97c4;
        box-shadow: 0 0 1em rgba(0,0,0,1);
        z-index: 10000;
        position: absolute;

    }
    .card-content {
        background: #eee;
    }
    .card-content.inside {
        background: #0b93d5;
    }
</style>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  SpentGroupItem --filename.css SpentGroupItem.css --filename.js SpentGroupItem.js --dist public/js/oscar/dist public/js/oscar/src/SpentGroupItem.vue
    export default {
        props: {
            spenttypegroup: {
                required: true
            },
            waitdrop: {
                required: true
            }
        },
        data(){
            return {
                dragged: false,
                inside: false
            }
        },
        methods: {
            startDrag(evt){
                this.dragged = true;
                this.$emit('drag', this.$el);
            },
            stopDrag(){
                this.dragged = false;
                this.$emit('stopdrag', this.spenttypegroup);
            }
        }
    }
</script>