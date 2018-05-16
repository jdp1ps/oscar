<template>
    <div class="event" :style="css"
         @mouseenter="handlerTooltipOn(event, $event)"
         @mouseleave="handlerTooltipOff(event, $event)"
         @mousedown="handlerMouseDown"
         :title="event.label"
         :class="{
             'event-changing': changing,
             'event-moving': moving,
             'event-selected': selected,
             'event-locked': isLocked,
             'status-external': isExternal,
             'status-info': isInfo,
             'status-draft': isDraft,
             'status-send' : isSend,
             'status-valid': isValid,
             'status-reject': isReject,
             'valid-sci': isValidSci,
             'valid-adm': isValidAdm,
             'reject-sci':isRejectSci,
             'reject-adm': isRejectAdm
         }">

        <div class="label" data-uid="UID">
            {{ event.label }}
        </div>

        <div class="description" v-if="!(isInfo || isExternal) ">
            <div class="submit-status">
                <span class="admin-status">
                    <i class="icon-archive icon-admin" :class="adminState"></i> Admin
                </span>
                <span class="sci-status">
                    <i class="icon-beaker icon-sci"></i> Scien.
                </span>
            </div>
        </div>

        <small>Durée : <strong>{{ labelDuration }}</strong> heure(s)</small>

        <div class="refus" @mouseover.prevent="showRefus != showRefus">
            <div v-show="showRefus">
                <i class="icon-beaker"></i>
                Refus scientifique :
                <div class="comment">{{ event.rejectedSciComment}}</div>
                <i class="icon-archive"></i>
                Refus administratif :
                <div class="comment">{{ event.rejectedAdminComment}}</div>
            </div>
        </div>

        <nav class="admin">
            <a href="#"
               @mousedown.stop.prevent=""
               @click.stop.prevent="handlerShowReject(event)"
               v-if="event.rejectedSciComment || event.rejectedAdminComment">
                <i class="icon-attention"></i>
                Afficher le rejet</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('editevent')" v-if="event.editable">
                <i class="icon-pencil-1"></i>
                Modifier</a>
            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('deleteevent')" v-if="event.deletable">
                <i class="icon-trash-empty"></i>
                Supprimer</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('submitevent')" v-if="event.sendable">
                <i class="icon-right-big"></i>
                Soumettre</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectscievent')" v-if="event.validableSci">
                <i class="icon-attention-1"></i>
                Refus scientifique</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('rejectadmevent')" v-if="event.validableAdm">
                <i class="icon-attention-1"></i>
                Refus administratif</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('validatescievent')" v-if="event.validableSci">
                <i class="icon-beaker"></i>
                Validation scientifique</a>

            <a href="#" @mousedown.stop.prevent="" @click.stop.prevent="$emit('validateadmevent')" v-if="event.validableAdm">
                <i class="icon-archive"></i>
                Validation administrative</a>

        </nav>

        <div class="bottom-handler" v-if="event.editable" @mousedown.prevent.stop="handlerStartMovingEnd">
            <span>===</span>
        </div>

        <time class="time start">{{ labelStart }}</time>
        <time class="time end">{{ labelEnd }}</time>
    </div>
</template>
<script>
    import moment from 'moment';

    export default {
        props: ['event', 'weekDayRef', 'withOwner', 'store'],

        data(){
            return {
                selected: false,
                moving: false,
                interval: null,
                movingBoth: true,
                changing: false,
                change: false,
                showRefus: false,
                labelStart: "",
                labelEnd: "",
                labelDuration: "",
                startX: 0
            }
        },

        filters: {
            hour(mm){
                return mm.format('H:mm')
            },
            dateFull(mm){
                return mm.format('D MMMM YYYY, h:mm')
            }
        },

        computed: {

            adminState(){
                return (
                    this.event.rejectedAdminAt ? 'rejected' : (this.event.validatedAdminAt ? 'validated' : 'waiting')
                )
            },

            sciStatus(){
                if (this.event.rejectedSciAt) {
                    return "Rejet administratif";
                }
                else if (this.event.validatedSciAt) {
                    return "Validation administrative le à";
                }
                else {
                    return "en attente de validation";
                }
            },

            css(){
                var marge = 0;
                var sizeless = 0;
                if (this.event.intersect > 0) {
                    sizeless = 3;
                    marge = sizeless / this.event.intersect * this.event.intersectIndex;
                }
                return {
                    'pointer-events': this.changing ? 'none' : 'auto',
                    height: (this.pixelEnd - this.pixelStart) + 'px',
                    background: this.withOwner ? this.colorLabel(this.event.owner) : this.colorLabel(this.event.label),
                    position: "absolute",
                    //'opacity': (this.changing ? '1' : 'inherit'),
                    top: this.pixelStart + 'px',
                    width: ((100 / 7) - 1) - sizeless + "%",
                    left: ((this.weekDay - 1) * 100 / 7) + marge + "%"
                }
            },

            ///////////////////////////////////////////////////////////////// STATUS
            isDraft(){
                return this.event.status == "draft";
            },
            isSend(){
                return this.event.status == "send";
            },
            isValid(){
                return this.event.status == "valid";
            },
            isValidSci(){
                return this.event.validatedSciAt != null;
            },
            isValidAdm(){
                return this.event.validatedAdminAt != null;
            },
            isRejectSci(){
                return this.event.rejectedSciAt != null;
            },
            isRejectAdm(){
                return this.event.rejectedAdminAt != null;
            },

            isReject(){
                return this.event.status == "reject";
            },

            isInfo(){
                return this.event.status == "info";
            },

            isExternal(){

                return (
                    this.event.status == "conges" ||
                    this.event.status == "formation" ||
                    this.event.status == "enseignement" ||
                    this.event.status == "external"
                );
            },

            isLocked(){
                return this.event.isLocked;
            },

            dateStart(){
                return moment(this.event.start);
            },

            dateEnd(){
                return moment(this.event.end);
            },

            pixelStart(){
                return this.dateStart.hour() * 40 + (40 / 60 * this.dateStart.minutes());
            },

            pixelEnd(){
                return this.dateEnd.hour() * 40 + (40 / 60 * this.dateEnd.minutes());
            },

            weekDay() {
                return this.dateStart.day()
            }
        },

        watch: {
            'event.start': function () {
                this.labelStart = this.dateStart.format('H:mm');
            },
            'event.end': function () {
                this.labelEnd = this.dateEnd.format('H:mm');
            }
        },

        methods: {
            formatDuration: function(milliseconde){
                var h = Math.floor(milliseconde / 60 / 60);
                var m = (milliseconde - (h * 60 * 60)) / 60;
                return h + (m ? 'h' + m : '');

            },

            handlerTooltipOn(event, e){
                console.log(this.store);
                this.store.tooltip = {
                    title: '<h3>' + event.label +'</h3>',
                    event: event,
                    x: '50px',
                    y: '50px'
                };
            },
            handlerTooltipOff(event, e){
                this.store.tooltip = "";
            },
            /**
             * Déclenche l'affichage du rejet.
             *
             * @param event
             */
            handlerShowReject(event){
                this.$emit('rejectshow', event);
            },

            updateWeekDay(value){
                var start = this.dateStart.day(value);
                var end = this.dateEnd.day(value);
                this.event.start = start.format();
                this.event.end = end.format();
            },

            handlerShowRefus(){
                bootbox.alert({
                    size: "small",
                    title: '<i class="icon-beaker"></i>   Refus scientifique',
                    message: '<em>Motif : </em>' + this.event.rejectedSciComment + ""
                })
            },
            handlerShowRefusAdmin(){
                bootbox.alert({
                    size: "small",
                    title: '<i class="icon-archive"></i>   Refus administratif',
                    message: '<em>Motif : </em>' + this.event.rejectedAdminComment + ""
                })
            },

            move(event){
                if (this.event.editable && event.movementY != 0) {
                    this.change = true;

                    var currentTop = parseInt(this.$el.style.top);
                    var currentHeight = parseInt(this.$el.style.height);

                    if (this.movingBoth) {
                        currentTop += event.movementY;
                        this.$el.style.top = currentTop + "px";
                    } else {
                        currentHeight += event.movementY;
                        this.$el.style.height = currentHeight + "px";
                    }

                    this.updateLabel();
                }
            },

            updateLabel(){
                var dtUpdate = this.topToStart();
                this.labelDuration = dtUpdate.duration;
                this.labelStart = dtUpdate.startLabel;
                this.labelEnd = dtUpdate.endLabel;
            },

            handlerEndMovingEnd(){
                if (this.movingBoth) {
                    this.movingBoth = false;
                }
            },

            handlerStartMovingEnd(e){
                /*this.movingBoth = false;
                 this.startMoving(e);*/
                this.$emit('onstartmoveend', this);
            },

            startMoving(e){
                if (this.event.editable) {
                    this.startX = e.clientX;
                    this.selected = true;
                    this.moving = true;
                    this.$el.addEventListener('mousemove', this.move);
                    this.$el.addEventListener('mouseup', this.handlerMouseUp);
                }
            },

            handlerMouseDown(e){
                if (this.event.editable) {
                    this.changing = true;
                    this.$emit('mousedown', this, e);
                }

            },

            handlerMouseUp(e){
                if (this.event.editable) {
                    this.moving = false;
                    this.$el.removeEventListener('mousemove', this.move);

                    var dtUpdate = this.topToStart();

                    this.event.start = this.dateStart
                        .hours(dtUpdate.startHours)
                        .minutes(dtUpdate.startMinutes)
                        .format();

                    this.event.end = this.dateEnd
                        .hours(dtUpdate.endHours)
                        .minutes(dtUpdate.endMinutes)
                        .format();

                    if (this.change) {
                        this.change = false;
                        this.$emit('savemoveevent', this.event);
                    }
                }
            },

            handlerMouseOut(e){
                this.handlerMouseUp()
            },

            roundMinutes(minutes){
                return Math.floor(60 / 40 * minutes / 15) * 15
            },

            formatZero(int){
                return int < 10 ? '0' + int : int;
            },

            ////////////////////////////////////////////////////////////////////////
            topToStart(){
                var round = 40 / 12;

                var minutesStart = parseInt(this.$el.style.top);
                var minutesEnd = minutesStart + parseInt(this.$el.style.height);

                var startHours = Math.floor(minutesStart / 40);
                var startMinutes = this.roundMinutes(minutesStart - startHours * 40);

                var endHours = Math.floor(minutesEnd / 40);
                var endMinutes = this.roundMinutes(minutesEnd - endHours * 40);

                return {
                    startHours: startHours,
                    startMinutes: startMinutes,
                    endHours: endHours,
                    endMinutes: endMinutes,
                    duration: this.formatDuration(((endHours * 60 + endMinutes) - (startHours * 60 + startMinutes)) * 60),
                    startLabel: this.formatZero(startHours) + ':' + this.formatZero(startMinutes),
                    endLabel: this.formatZero(endHours) + ':' + this.formatZero(endMinutes)
                };
            }
        },

        mounted(){
            console.log("TEST", this.formatDuration);
            this.labelStart = this.dateStart.format('H:mm');
            this.labelEnd = this.dateEnd.format('H:mm');
            this.labelDuration = this.formatDuration(this.dateEnd.unix() - this.dateStart.unix());
        }
    }
</script>