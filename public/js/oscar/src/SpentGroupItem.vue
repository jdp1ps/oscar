<template>

        <article class="card spenttype"
                 :class="{ 'movable': mode == 'move', 'selectable': selection && selection != spenttypegroup, 'selected': selection == spenttypegroup, 'hover': hover }"
                 @click.stop.prevent="handlerClick"
                 @mousehover="handlerOver(true)"
                 @mouseout="handlerOver(false)"

        >
            <h3 class="card-title">
                <span class="handler">

                    <i class="icon-angle-down" @click="open = false" v-if="open"></i>
                    <i class="icon-angle-right" @click="open = true" v-else></i>


                    <strong class="code" :class="{ 'blind' : spenttypegroup.blind }">{{ spenttypegroup.code }}</strong>
                    {{ spenttypegroup.label }}

                    <span v-for="aLabel, a in annexes" :title="aLabel" class="annexe">{{ a }}</span>
                    <strong :class="'annexe-' + spenttypegroup.annexe" v-if="spenttypegroup.annexe">{{ spenttypegroup.annexe }}</strong>


                </span>
                <p class="small">{{ spenttypegroup.description }}</p>
                <small>
                    <a href="#" @click.prevent="$emit('blind', spenttypegroup)">
                        <i class="icon-eye-off"></i>
                        <span v-if="spenttypegroup.blind">Utiliser</span>
                        <span v-else>Ne pas utiliser</span>
                    </a>

                    <a href="#" @click.prevent="$emit('edit', spenttypegroup)">
                        <i class="icon-pencil"></i>
                        Éditer</a>

                    <a href="#" @click.prevent="$emit('delete', spenttypegroup)">
                        <i class="icon-trash"></i>
                        Supprimer</a>

                    <a href="#" @click.prevent="$emit('new', spenttypegroup)">
                        <i class="icon-doc-add"></i>
                        Nouveau</a>
                </small>
            </h3>
            <div class="card-content spentarea" v-if="!spenttypegroup.blind" v-show="open">
                <spenttypeitem v-for="s in spenttypegroup.children"
                               :annexes="annexes"
                               :spenttypegroup="s"
                               :waitdrop="waitdrop"
                               :key="s.id"
                               :mode="mode"
                               :selection="selection"
                               @edit="$emit('edit', $event)"
                               @selection="$emit('selection', $event)"
                               @destination="$emit('destination', $event)"
                               @blind="$emit('blind', $event)"
                               @delete="$emit('delete', $event)"
                               @new="$emit('new', $event)"
                />
            </div>
        </article>
</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  SpentGroupItem --filename.css SpentGroupItem.css --filename.js SpentGroupItem.js --dist public/js/oscar/dist public/js/oscar/src/SpentGroupItem.vue
    export default {
        props: {
            spenttypegroup: {
                required: true
            },
            selection: {
                required: true
            },
            mode: {
                required: true
            },
            annexes: {
                required: true
            }
        },
        data(){
            return {
                movable: false,
                selectable: false,
                hover: false,
                open: true
            }
        },

        methods: {
            handlerClick(){
                if( this.mode == 'selection' ){
                    this.$emit('selection', this.spenttypegroup);
                }
                if( this.mode == 'destination' ){
                    console.log("destination");
                    this.$emit('destination', this.spenttypegroup);
                }
            },

            handlerOver(direction){
                this.hover = direction;
            }
            // handlerDrag(evt){
            //     //console.log("Drag");
            //     // evt.target.classList.add('dragged');
            // },
            // handlerDragStart(evt){
            //     this.dragged = true;
            //     this.$emit('dragitem', this.spenttypegroup);
            // },
            // handlerDragEnd(evt){
            //     console.log("DragEnd");
            //     evt.target.classList.remove('dragged');
            //     this.dragged = false;
            // },
            // handlerDragEnter(evt){
            //     if( evt.target.classList.contains('card-content') ){
            //         evt.preventDefault();
            //         this.inside = true;
            //     }
            // },
            // handlerDragOver(evt){
            //
            // },
            // handlerDrop(evt){
            //     console.log("DROP", this.spenttypegroup.label);
            //     this.inside = false;
            //     this.$emit('dropitem', this.spenttypegroup);
            // },
            // handlerDragLeave(evt){
            //     if( evt.target.classList.contains('card-content') ){
            //         evt.preventDefault();
            //         this.inside = false;
            //     }
            // }
        }
    }
</script>