<template>
    <div  class="activity" style="" :style="{ 'left': a.left +'px', 'width': a.width+'px'}">
        <span @click="open = !open">
            <i class="icon-angle-right"></i>
            <abbr title="">{{ a.acronym }}</abbr>
            <strong><i class="icon-cube"></i> {{ a.label }}</strong>
        </span>

        <div v-if="open">
            <small>
                du {{ a.dateStart }} au {{ a.dateEnd }} <br>
                ( {{ a.duration }} mois)
            </small>
            <pre>
                {{ a }}
            </pre>
        </div>

        <div v-for="p in a.persons">
            <i class="icon-user"></i>
            {{ p.displayName }}
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            activity: {
                required: true
            }
        },

        data(){
            return {
                open: false
            }
        },

        computed: {
            a(){
                return this.activity
            }
        }
    }
</script>