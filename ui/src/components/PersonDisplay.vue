<template>
  <span class="person" v-if="person" @mouseenter="handlerEnter(person, $event)" @mouseleave="handlerLeave(person)">
    <em class="firstname">{{ person.firstname }}</em>&nbsp;
    <strong class="lastname text-private">{{ person.lastname }}</strong>
  </span>
  <em v-else>
    Inconnu
  </em>
</template>
<script>
import GlobalModel from "../models/GlobalModel.js";

let tempo = null;

export default {
  name: 'PersonDisplay',
  props: {
    person: { required: false, type: Object },
    allowTooltip: { default: false },
    timing: { default: 750, type: Number },
  },
  methods: {
    handlerEnter(person, event) {
      if( this.allowTooltip ) {
        GlobalModel.dispatch('tooltipPersonPreShooting', { id: person.id, event: event });
        if( person && person.id ) {
          if( tempo === null ) {
            let evt = event;
            tempo = setTimeout(() => {
              this.setPerson(person.id, evt);
            }, this.timing);
          }
        }
      }
    },
    handlerLeave(person) {
      if( this.allowTooltip ) {
        if (person && person.id) {
          clearTimeout(tempo);
          tempo = null;
          GlobalModel.dispatch('tooltipReset');
        }
      }
    },
    setPerson(personId, event) {
      GlobalModel.dispatch('tooltipPerson', { 'type': 'person', 'id': personId, 'event': event });
    }
  }
}
</script>