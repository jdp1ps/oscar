<template>

        <article class="card spenttype" :class="{ 'dragged': dragged, 'inside': inside }" draggable="true"
                 @dragend.stop="handlerDragEnd"
                 @dragstart.stop="handlerDragStart"
                 @drag="handlerDrag"
        >
            <h3 class="card-title">
                <span class="handler">
                    [{{ spenttypegroup.id}} ]
                    {{ spenttypegroup.label }}
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
            <div class="card-content"
                 @drop.stop="handlerDrop"
                 @dragenter.stop.prevent="handlerDragEnter"
                 @dragleave.stop.prevent="handlerDragLeave"
                 @dragover.stop.prevent="handlerDragOver">
                <spenttypeitem v-for="s in spenttypegroup.children"
                               :spenttypegroup="s"
                               :waitdrop="waitdrop"
                               :key="s.id"
                               @edit="$emit('edit', $event)"
                               @dragitem="$emit('dragitem', $event)"
                               @dropitem="$emit('dropitem', $event)"
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
    .spenttype {
        -moz-user-select: none;
        -khtml-user-select: none;
        -webkit-user-select: none;
        user-select: none;
        /* Required to make elements draggable in old WebKit */
        -khtml-user-drag: element;
        -webkit-user-drag: element;
    }
    .spenttype .handler {
        cursor: pointer;
    }
    .dragged {
        background: #0b97c4;
        box-shadow: 0 0 1em rgba(0,0,0,.2);
        opacity: .7;
    }
    .dragged .card-content {
        pointer-events: none;
    }
    .inside .card-content {
        border: 2px dotted #FF6600;
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
            handlerDrag(evt){
                //console.log("Drag");
                // evt.target.classList.add('dragged');
            },
            handlerDragStart(evt){
                this.dragged = true;
                this.$emit('dragitem', this.spenttypegroup);
            },
            handlerDragEnd(evt){
                console.log("DragEnd");
                evt.target.classList.remove('dragged');
                this.dragged = false;
            },
            handlerDragEnter(evt){
                if( evt.target.classList.contains('card-content') ){
                    evt.preventDefault();
                    this.inside = true;
                }
            },
            handlerDragOver(evt){

            },
            handlerDrop(evt){
                console.log("DROP", this.spenttypegroup.label);
                this.inside = false;
                this.$emit('dropitem', this.spenttypegroup);
            },
            handlerDragLeave(evt){
                if( evt.target.classList.contains('card-content') ){
                    evt.preventDefault();
                    this.inside = false;
                }
            }
        }
    }
</script>