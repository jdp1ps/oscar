<template>
    <article class="card xs jalon" :class="cssClass">
        <time :datetime="milestone.dateStart">
            {{ milestone.dateStart | moment }}
        </time>
        <strong class="card-title">{{ milestone.type.label }}</strong>
        <p class="details" v-if="milestone.comment">{{ milestone.comment }}</p>
        <nav>

            <a href="#" v-if="cancelFinish"
               title="Marquer comme non-terminé"
               @click.prevent="$emit('unvalid', milestone)">
                <i class="icon-cancel-outline"></i>
            </a>

            <a href="#" v-if="finishable"
               title="Marquer comme terminé"
               @click.prevent="$emit('valid', milestone)">
                <i class="icon-ok-circled"></i>
            </a>

            <a href="#"
                    title="Supprimer ce jalon"
                    @click.prevent="$emit('remove', milestone)"
                    v-if="milestone.deletable">
                <i class="icon-trash"></i>
            </a>
            <a href="#"
                    title="Modifier ce jalon"
                    @click.prevent="$emit('edit', milestone)"
                    v-if="milestone.editable">
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
                return this.milestone.validable && this.milestone.type.finishable == true && this.milestone.finished < 100;
            },

            cancelFinish(){
                return this.milestone.validable && this.milestone.type.finishable == true && this.milestone.finished > 0
            },

            finished(){
                return this.milestone.type.finishable == true && this.milestone.finished == 100
            },

            late(){
                return this.finishable == true && this.milestone.past && !this.finished;
            },

            past(){
                return this.milestone.past
            },

            cssClass(){
                let css = {
                    'finishable': this.finishable,
                    'finished': this.finished,
                    'late': this.late,
                    'past': this.past,
                };
                css[this.milestone.type.facet] = true;
                return css;
            }
        }
    }
</script>