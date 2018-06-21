<template>
    <div class="day" @click="handlerClick" :class="{'locked': day.locked}">
        <span class="label">{{ day.label }}</span>

        <span class="cartouche secondary1 xs" v-for="d in groupProject" :title="d.label">
            <em>{{ d.acronym }}</em>
            <span class="addon">
                {{d.duration}}
            </span>
        </span>

        <span class="cartouche orange xs" v-if="day.teaching.length" title="Enseignement">
            Cours
            <span class="addon">
                {{totalEnseignement}}
            </span>
        </span>

        <span class="cartouche info xs" v-if="day.infos.length" title="Infos">
            Infos
            <span class="addon">
                {{totalInfo}}
            </span>
        </span>

        <span class="cartouche success xs" v-if="day.training.length" title="Infos">
            Formation
            <span class="addon">
                {{totalFormation}}
            </span>
        </span>

        <span class="cartouche complementary xs" v-if="day.vacations.length" title="Infos">
            Congès
            <span class="addon">
                {{totalVacations}}
            </span>
        </span>

        <span v-if="day.locked" :title="day.lockedReason">
            <i class="icon-lock"></i>
            Verrouillé
        </span>
        &nbsp;
    </div>
</template>

<script>
    export default {
        props: {
            day: {
               require: true
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
            totalVacations(){
                let t = 0.0;
                this.day.vacations.forEach(d => {
                    t += d.duration;
                });
                return t;
            },
           groupProject(){
               let groups = {};
               this.day.declarations.forEach(d => {
                   if( !groups.hasOwnProperty(d.acronym) ){
                       groups[d.acronym] = {
                           label: d.label,
                           acronym: d.acronym,
                           duration: 0.0
                       }
                   }
                   groups[d.acronym].duration += d.duration;
               });
               return groups;
           }
        },

        methods: {
            handlerClick(){
                this.$emit('selectDay', this.day);
            }
        }

    }
</script>