<template>
    <article class="workpackage-person">
        <div class="displayname">
            <strong>{{ person.person.displayname }}</strong>
            <a href="#" @click.prevent="handlerRemove(person)" class="link" v-if="editable && mode == 'read'" title="Supprimer ce déclarant"><i class="icon-trash"></i></a>
        </div>
        <div class="tempsdeclare temps">
            <div v-if="editable && mode == 'edit'">
                Heures prévues :
                <input type="integer" v-model="durationForm" style="width: 5em" @keyup.13="handlerUpdate"/>
                <a href="#" @click.prevent="handlerUpdate" title="Appliquer la modification des heures prévues"><i class="icon-floppy"></i></a>
                <a href="#" @click.prevent="handlerCancel" title="Annuler la modification des heures prévues"><i class="icon-cancel-outline"></i></a>
            </div>
            <span v-else>
                        <strong class="wp-hours">
                            <span title="Heure(s) saisie(s)" class="wp-hour unsend">{{ person.unsend | heures }}</span>
                            <span title="Heure(s) validée(s)" class="wp-hour validate">{{person.validate | heures}}</span>
                            <span title="Heure(s) en cours de validation" class="wp-hour validating" v-if="person.validating > 0">{{person.validating | heures}}</span>
                            <span title="Heure(s) en conflit" class="wp-hour conflicts" v-if="person.conflicts > 0">{{person.conflicts | heures}}</span>
                            <span title="Heure(s) à valider" class="wp-hour duration">
                                / {{person.duration}}
                                <a href="#" @click.prevent="handlerEdit" v-if="editable && mode == 'read'" title="Modifier les heures prévues"><i class="icon-pencil"></i></a>
                            </span>
                        </strong>
                    </span>
        </div>
    </article>
</template>
<script>
    export default {
        props: {
            'person': { default: function(){ return {} } },
            'editable': false
        },
        computed: {
            duration(){
                return this.person.duration;
            }
        },
        data(){
            return {
                'canSave': false,
                'mode' : 'read',
                'durationForm': 666
            }
        },
        methods: {
            handlerKeyUp(){
                console.log(arguments);
            },
            handlerUpdate(){
                this.$emit('workpackagepersonupdate', this.person, this.durationForm);
                this.mode = 'read';
            },
            handlerEdit(){
                this.mode = 'edit';
                this.durationForm = this.person.duration;
            },
            handlerCancel(){
                this.mode = 'read';
                this.durationForm = this.person.duration;
            },
            handlerRemove(){
                this.$emit('workpackagepersondelete', this.person);
            }
        }
    }
</script>