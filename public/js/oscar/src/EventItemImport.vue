<template>
    <article class="list-item" :class="{ imported: event.imported }" :style="css" @click="event.imported = !event.imported">
        <time class="start">{{ beginAt }}</time> -
        <time class="end">{{ endAt }}</time>
        <span>
                  <em>{{ event.label }}</em>
                  <strong v-show="event.useLabel"> => {{ event.useLabel }}</strong>
                  </span>
    </article>
</template>
<script>
    export default {
        props: ['event'],
        computed: {
            beginAt(){
                return this.event.mmStart.format('HH:mm');
            },
            endAt(){
                return this.event.mmEnd.format('HH:mm');
            },
            css(){
                var percentUnit = 100 / (18 * 60)
                    , start = (this.event.mmStart.hour() - 6) * 60 + this.event.mmStart.minutes()
                    , end = (this.event.mmEnd.hour() - 6) * 60 + this.event.mmEnd.minutes();

                return {
                    position: "absolute",
                    left: (percentUnit * start) + '%',
                    width: (percentUnit * (end - start)) + '%',
                    background: this.colorLabel(this.event.label)
                }
            }
        }
    }
</script>