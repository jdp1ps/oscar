<template>
  <span class="btn-group" v-if="sticky.length">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="icon-cube"></i> Accès rapide
      <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
      <li v-for="a in sticky">
        <a :href="a.location">
          <i class="icon-cube"></i>
          <strong>{{ a.num }}</strong>
          <em>{{ a.label }}</em>
          <i class="icon-link-ext"></i>
        </a>
      </li>
    </ul>
  </span>

</template>
<script>

const storage_key = 'activities_sticky';

export default {
  name: 'Sticky',

  computed: {
    storage() {
      return localStorage.getItem(storage_key);
    },
    isSticky() {
      return this.sticky.find(item => item.id == this.activity.infos.id);
    }
  },

  data() {
    return {
      sticky: [],
    }
  },

  methods: {
  ////////////////////////////////////////// Système d'épingle

    handlerPurgeSticky() {
      this.sticky = [];
      localStorage.removeItem(storage_key);
    },

    toogleSticky() {
      if( this.isSticky ){
        this.handlerUnSticky()
      } else {
        this.handlerSticky()
      }
    },

    handlerUnSticky() {
      this.sticky.forEach((item, id) => {
        if( item.id == this.activity.infos.id ){
          this.sticky.splice(id, 1);
        }
      })
      localStorage.setItem(storage_key, JSON.stringify(this.sticky));
    },

    handlerSticky() {
      this.sticky.push({
        id: this.activity.infos.id,
        label: this.activity.infos.label,
        num: this.activity.infos.numOscar,
        location: document.location.href,
      });
      localStorage.setItem(storage_key, JSON.stringify(this.sticky));
    },

    handlerNavigateSticky(sticked){
      if(sticked.location) {
        document.location = sticked.location;
      }
    }
  },
  mounted() {

    let saved = localStorage.getItem(storage_key);
    if (saved) {
      saved = JSON.parse(saved);
    } else {
      saved = [];
    }
    this.sticky = saved;
  }
}
</script>