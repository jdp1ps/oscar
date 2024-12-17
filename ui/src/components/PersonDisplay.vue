<template>
  <span class="person" v-if="person">
    <em class="firstname">{{ person.firstname }}</em>&nbsp;
    <strong class="lastname text-private">{{ person.lastname }}</strong>
  </span>
  <em v-else>
    Inconnu
  </em>
</template>
<script>
export default {
  name: 'PersonDisplay',
  props: {
    person: { required: false, type: Object },
    allowTooltip: { default: false },
  }
}
</script>