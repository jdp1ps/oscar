<template>
    <div class="day" @click="handlerClick" @click.shift.prevent.stop="$emit('debug', day)" :class="{'locked': day.locked}" @contextmenu.prevent="handlerRightClick">



        <span class="label">{{ day.i }}</span>

        <span class="cartouche wp xs" v-for="d in groupProject" :title="d.label" :class="{ 'conflict': d.status_id == 3 }">
            <i :class="'icon-status-' + d.status_id"></i>
            {{ d.acronym }}
            <span class="addon">
                {{d.duration | duration2(day.dayLength)}}
            </span>
        </span>



        <span >
            <span v-for="other in day.othersWP" class="cartouche xs" :class="other.code">
                <i class="icon-status-2" v-if="other.validations == null"></i>
                <i class="icon-status-1" v-else-if="other.validations.status == 'valid'"></i>
                <i class="icon-status-3" v-else-if="other.validations.status == 'conflict'"></i>
                <i class="icon-status-5" v-else></i>
                {{ other.label }}
                <span class="addon">
                    {{other.duration | duration2(day.dayLength)}}
                </span>
            </span>

        </span>


        <span v-if="day.closed" :title="day.lockedReason" style="font-size: .7em">
            <i class="icon-minus-circled"></i>
            Fermé
        </span>

        <span v-else-if="day.locked" :title="day.lockedReason" style="font-size: .7em">
            <i class="icon-lock"></i>
            Verrouillé
        </span>
        &nbsp;
    </div>
</template>

<style scoped lang="scss">
    .cartouche {
        white-space: nowrap;
        margin: 0;
        border-radius: 2px;
        box-shadow: none;
        font-size: .75em;
        &.training { background-color: #0b58a2; }
        &.sickleave { background-color: #808000; }
        &.wp { background-color: #6a5999; }
        &.conflict { background: #AA0000; }
    }

</style>

<script>

    export default {
        name: 'TimesheetMonthDay',

        props: {
            others: { required: true },
            day: { require: true }
        },

        filters: {
            duration(v){
                let h = Math.floor(v);
                let m = Math.round((v - h)*60);
                if( m < 10 ) m = '0'+m;
                return h +':' +m;
            }
        },

        computed: {
            totalEnseignement(){
                let t = 0.0;
                this.day.teaching.forEach(d => {
                    t += d.duration;
                });
                return t;
            },
            totalInfo(){
                let t = 0.0;
                this.day.infos.forEach(d => {
                    t += d.duration;
                });
                return t;
            },
            totalFormation(){
                let t = 0.0;
                this.day.training.forEach(d => {
                    t += d.duration;
                });
                return t;
            },
            totalConges(){
                let t = 0.0;
                this.day.conges.forEach(d => {
                    t += d.duration;
                });
                return t;
            },
            totalSickleave(){
                let t = 0.0;
                this.day.sickleave.forEach(d => {
                    t += d.duration;
                });
                return t;
            },
            totalAbsent(){
                let t = 0.0;
                this.day.absent.forEach(d => {
                    t += d.duration;
                });
                return t;
            },
            totalResearch(){
                let t = 0.0;
                this.day.research.forEach(d => {
                    t += d.duration;
                });
                return t;
            },
           groupProject(){
               let groups = {};
               if( this.day.declarations ) {
                   this.day.declarations.forEach(d => {
                       if (!groups.hasOwnProperty(d.acronym)) {
                           groups[d.acronym] = {
                               label: d.label,
                               acronym: d.acronym,
                               duration: 0.0,
                               status_id: d.status_id
                           }
                       }
                       if (d.status_id != groups[d.acronym].status_id) {
                           groups[d.acronym].status_id = 0;
                       }
                       //groups[d.acronym].status_id += d.duration;
                       groups[d.acronym].duration += d.duration;
                   });
               }
               return groups;
           }
        },

        methods: {
            totalOther(code){
                let t = 0.0;
                this.day[code].forEach(d => {
                    t += d.duration;
                });
                return t;
            },
            handlerClick(){
                this.$emit('selectDay', this.day);
            },

            handlerRightClick(e){
                this.$emit('daymenu', e, this.day);
            }
        }

    }
</script>