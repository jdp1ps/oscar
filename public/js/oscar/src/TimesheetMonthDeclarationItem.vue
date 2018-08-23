<template>
    <article class="card card-xs xs wp-duration" :class="'status-' + d.status_id">
        <span class="infos">
            <strong>
                <i class="icon-archive"></i>
                <abbr :title="d.project">{{ d.acronym }}</abbr>
                <i class="icon-angle-right"></i> {{ d.wpCode }}
                <i class="icon-comment" :class="d.comment ? 'with-comment' : ''" :title="d.comment"></i>
            </strong><br>
            <small><i class="icon-cubes"></i> {{ d.label }}</small>

            <div class="status">
                <small v-if="d.status_id == 1"><i class="icon-ok-circled"></i>Validée</small>
                <small v-else-if="d.status_id == 2"><i class="icon-pencil"></i> Brouillon</small>
                <small v-else-if="d.status_id == 3"><i class="icon-attention-circled"></i> Rejeté</small>
                <small v-else-if="d.status_id == 5"><i class="icon-paper-plane"></i>En cours de validation</small>
                <small v-else><i class="icon-help-circled"></i> Autre status ({{d.status_id}})</small>
                <span v-if="d.validations.conflict" class="text-danger">
                    {{ d.validations.conflict }}
                </span>
             </div>
        </span>
        <div class="total">
            {{ d.duration | duration2(dayLength) }}
        </div>
       <div class="left buttons-icon">
            <i class="icon-trash" @click="$emit('removetimesheet', d)" :class="d.credentials.deletable != true ? 'disabled':''"></i>
            <i class="icon-edit" @click="$emit('edittimesheet', d)"  :class="d.credentials.editable != true ? 'disabled':''"></i>
            <i class="icon-bug" @click="$emit('debug', d)"></i>
        </div>
    </article>
</template>
<script>
    export default {
        name: 'TimesheetMonthDeclarationItem',
        props: {
            'd': { required: true },
            'dayLength': { required: true }
        },
        filters: {
            heures(v){
                let heures = Math.floor(v);
                let minutes = Math.round((v - heures)*60);
                if( minutes < 10 ) minutes = '0'+minutes;
                console.log(v, ' => ',heures,'h',minutes);
                return heures+":"+minutes;
            }
        }
    }
</script>