<template>
    <article class="card xs jalon" :class="cssClass">
        <time :datetime="milestone.dateStart">
            {{ milestone.dateStart | moment }}
        </time>
        <strong class="card-title">{{ milestone.type.label }}</strong>
        <p class="details" v-if="milestone.comment">{{ milestone.comment }}</p>
        <nav>
            <a href="#"
                    class="btn-valid"
                    title="Marquer comme terminé"
                    @click.prevent="$emit('valid', milestone)"
                    >
                <i class="icon-ok-circled"></i>
            </a>
            <a href="#" class="btn-delete" title="Supprimer ce jalon" @click.prevent="$emit('remove', milestone)">
                <i class="icon-trash"></i>
            </a>
            <a href="#" class="btn-edit" title="Modifier ce jalon" @click.prevent="$emit('edit', milestone)">
                <i class="icon-edit"></i>
            </a>
        </nav>
    </article>
</template>
<script>
    /**
     * Jalon (item de la liste)
     */
    export default {
        props:{
            milestone: {
                required: true
            }
        },
        computed: {
            finishable(){
                return this.milestone.type.finishable == true
            },

            finished(){
                return this.milestone.finished == 100
            },

            late(){
                return this.finishable && this.milestone.past && !this.finished;
            },

            past(){
                return this.milestone.past
            },

            cssClass(){
//                let css = {}
//                css.past = this.milestone.past;
//
//                if( this.milestone.type && this.milestone.type.facet )
//                    css[this.milestone.type.facet] = true;
//
//                if( this.finishable ){
//                    if( this.finished == 100 )
//                        css['finished'] = true;
//                    else
//                        css['unfinished'] = true;
//                }


                return {
                    'finishable': this.finishable,
                    'finished': this.finished,
                    'late': this.late,
                    'past': this.past,

                };
            }
        }
    }
</script>