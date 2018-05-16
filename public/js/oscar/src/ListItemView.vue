<template>
    <article class="list-item" :style="css" :class="{
                    'event-editable': event.editable,
                    'status-info': event.isInfo,
                    'status-external': event.isExternal,
                    'status-draft': event.isDraft,
                    'status-send' : event.isSend,
                    'status-valid': event.isValid,
                    'status-reject': event.isReject,
                    'valid-sci': event.isValidSci,
                    'valid-adm': event.isValidAdm,
                    'reject-sci':event.isRejectSci,
                    'reject-adm': event.isRejectAdm
                    }">
        <time class="start">{{ beginAt }}</time> -
        <time class="end">{{ endAt }}</time>
        <strong>{{ event.label }}</strong>
        <div class="details">
            <h4>
                <i class="picto" :style="{background: color}"></i>
                {{ event.label }} {{ event.status }}</h4>
            <p class="time">
                de <time class="start">{{ beginAt }}</time> à <time class="end">{{ endAt }}</time>, <em>{{ event.duration }}</em> heure(s) ~ état : <em>{{ event.status }}</em>
            </p>
            <p class="small description">
                {{ event.description }}
            </p>

            <nav>
                <button class="btn btn-default btn-xs" @click="$emit('selectevent', event)">
                    <i class="icon-calendar"></i>
                    Voir la semaine</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('editevent', event)" v-if="event.editable">
                    <i class="icon-pencil-1"></i>
                    Modifier</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('submitevent', event)" v-if="event.sendable">
                    <i class="icon-right-big"></i>
                    Soumettre</button>

                <button class="btn btn-primary btn-xs"  @click="$emit('deleteevent', event)" v-if="event.deletable">
                    <i class="icon-trash-empty"></i>
                    Supprimer</button>

                <button class="btn btn-danger btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectscievent')" v-if="event.validableSci">
                    <i class="icon-attention-1"></i>
                    Refus scientifique</button>

                <button class="btn btn-danger btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectadmevent')" v-if="event.validableAdm">
                    <i class="icon-attention-1"></i>
                    Refus administratif</button>

                <button class="btn btn-success btn-xs"  @mousedown.stop.prevent="" @click.stop.prevent="$emit('validatescievent')" v-if="event.validableSci">
                    <i class="icon-beaker"></i>
                    Validation scientifique</button>

                <button class="btn btn-success btn-xs" @mousedown.stop.prevent="" @click.stop.prevent="$emit('validateadmevent')" v-if="event.validableAdm">
                    <i class="icon-archive"></i>
                    Validation administrative</button>
            </nav>
        </div>
    </article>
</template>
<script>
    export default {
        props: ['event', 'withOwner'],
        methods: {
            handlerValidate(){
                this.$emit('validateevent')
            }
        },
        computed: {
            beginAt(){
                return this.event.mmStart.format('HH:mm');
            },
            endAt(){
                return this.event.mmEnd.format('HH:mm');
            },
            cssClass(){

                return 'status-' + this.event.status;
            },
            color(){
                return this.colorLabel(this.event.label);
            },
            css(){
                var percentUnit = 100 / (18 * 60)
                    , start = (this.event.mmStart.hour() - 6) * 60 + this.event.mmStart.minutes()
                    , end = (this.event.mmEnd.hour() - 6) * 60 + this.event.mmEnd.minutes();

                return {
                    top: this.event.decaleY*1.75 +"em",
                    left: (percentUnit * start) + '%',
                    width: (percentUnit * (end - start)) + '%',
                    background: this.color
                }
            }
        }
    }
</script>