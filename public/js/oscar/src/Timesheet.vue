<template>
    <div class="timesheet">

        <div class="activity">
            <strong>
                <i class="icon-cubes"></i>
                {{ timesheet.project_acronym }}
            </strong>
            <small>
                <i class="icon-cube"></i>
                {{ timesheet.activity_label }}
            </small>
        </div>

        <div class="wp" :title="timesheet.workpackage_label">
            <i class="icon-archive"></i>
            {{ timesheet.workpackage_code }}
        </div>

        <div class="date">
            <div class="jour">{{ jour }}</div>
            <div class="horaire">{{ start }} à {{ end }}</div>
            <div class="duree">
                <i class="icon-stopwatch"></i>{{ duree }} heures
            </div>
        </div>


        <div class="declarant">
            <i class="icon-user"></i>
            {{ timesheet.owner }}
        </div>

        <div>
            <span class="small-note" v-if="timesheet.validatedSciBy">
                <i class="icon-beaker"></i>
                Validée par <strong>{{ timesheet.validatedSciBy }}</strong>
            </span>
            <span v-else>
                <nav class="btn-group btn-group-xs" v-if="timesheet.validableSci">
                    <button class="btn btn-xs btn-success" @click="$emit('validsci', timesheet)" title="Valider scientifiquement ce créneau">
                        <i class="icon-beaker"></i>
                        Valider
                        </button>
                    <button class="btn btn-xs btn-danger" @click="$emit('rejectsci', timesheet)" title="Refuser scientifiquement ce créneau">
                        <i class="icon-minus-circled"></i>
                        Refuser
                    </button>
                </nav>
                <span class="small-note" v-else>
                    <i class="icon-beaker"></i>
                    En attente de la validation scientifique
                </span>
            </span>
        </div>

        <div>
            <span v-if="timesheet.validatedAdminBy" :title="'Ce créneau a été validé administrativement par ' + timesheet.validatedAdminBy">
                <span class="small-note" >
                    <i class="icon-archive"></i>
                    Validée par <strong>{{ timesheet.validatedAdminBy }}</strong>
                </span>
            </span>
            <span v-else>
                <nav class="btn-group btn-group-xs" v-if="timesheet.validableAdm">
                    <button class="btn btn-xs btn-danger" @click="$emit('rejectadm', timesheet)" title="Refuser administrativement ce créneau">
                        <i class="icon-minus-circled"></i>
                        Refuser
                    </button>
                    <button class="btn btn-xs btn-success" @click="$emit('validadm', timesheet)" title="Valider administrativement ce créneau">
                        <i class="icon-ok-circled"></i>
                        Valider
                    </button>
                </nav>
                <span class="small-note" v-else>
                    <i class="icon-beaker"></i>
                    En attente de la validation administrative
                </span>
            </span>
        </div>
    </div>
</template>

<script>
    // Externe
    const moment = import('moment');

    moment.locale('FR_fr');

    export default {
        name:'timesheet',
        props: ['timesheet'],
        computed: {
            start(){
              return moment(this.timesheet.start).format('HH:mm')
            },
            end(){
                return moment(this.timesheet.end).format('HH:mm')
            },
            jour(){
              return moment(this.timesheet).format('dddd D MMMM YYYY')
            },
            duree(){
                let fin = moment(this.timesheet.end),
                    debut = moment(this.timesheet.start);
                return moment(fin.diff(debut)).format('HH:mm')
            }
        }
    }
</script>
