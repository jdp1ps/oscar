<template>
    <div class="day"
         @click="handlerClick"
         :class="{'locked': day.locked, 'error': (day.total > day.maxLength)}"
         >

        <span class="label">
            {{ day.i }}
        </span>

        <span v-if="day.total > day.maxLength" class="text-danger">
            <i class="icon-attention"></i>
            Erreur
        </span>

        <span class="cartouche wp xs" v-for="d in groupProject" :title="d.label" :style="{ 'background-color': d.color }">
            <i v-if="d.status_id == null" class="icon-draft"></i>
            <i v-else :class="'icon-' + d.status_id"></i>
            {{ d.acronym }}
            <span class="addon">
                {{d.duration | duration2(day.dayLength)}}
            </span>
        </span>



        <span >
            <span v-for="other in day.othersWP" class="cartouche xs" :class="other.code">
                <i v-if="other.validations == null" class="icon-draft"></i>
                <i :class="'icon-' + other.status_id" v-else></i>
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

<script>

    export default {
        name: 'TimesheetMonthDay',

        props: {
            others: { required: true },
            day: { required: true },
            projectscolors: { required: true, default: null }
        },

        data(){
            return {
                colors: ["#093b8c",
                    "#098c29",
                    "#8c2109",
                    "#4c098c",
                    "#8c0971",
                    "#8c6f09"]
            }
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
           groupProject(){
               let groups = {};
               if( this.day.declarations ) {
                   this.day.declarations.forEach(d => {
                       if (!groups.hasOwnProperty(d.acronym)) {
                           groups[d.acronym] = {
                               label: d.label,
                               acronym: d.acronym,
                               duration: 0.0,
                               status_id: d.status_id,
                               color: this.getProjectColor(d.acronym)
                           }
                       }

                       //groups[d.acronym].status_id += d.duration;
                       groups[d.acronym].duration += d.duration;
                   });
               }
               return groups;
           }
        },

        methods: {
            /**
             *
             * @param acronym
             */
            getProjectColor(acronym){
                if( this.projectscolors && this.projectscolors.hasOwnProperty(acronym) ){
                    return this.projectscolors[acronym];
                }
                else {
//                    let rand = Math.floor(Math.random()*this.colors.length);
                    return '#8c0971'
                }
            },

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