<template>
  <transition name="fade" mode="out-in">
  <div class="oscar-tooltip" v-if="displayed" :style="{left: x, top: y}" @mouseenter="handlerInside" @mouseleave="handlerOutside">
    <div v-if="tooltipInfo && tooltipInfo.type === 'person'">
      <h3>
        <img :src="'https://gravatar.com/avatar/' + tooltipInfo.gravatar" alt="" width="30" height="30" class="gravatar-img" />
        <span class="fullname">
          {{ tooltipInfo.firstname }}
          <strong class="text-private lastname">
            {{ tooltipInfo.lastname }}
          </strong>
        </span>
      </h3>
      <div class="content">
        <div class="email">
          <i class="icon-mail"></i>
          {{ tooltipInfo.email }}
        </div>
        <div class="location">
          <i class="icon-location"></i>
          {{ tooltipInfo.location || 'Inconnue' }}
        </div>
        <div class="email">
          <i class="icon-building-filled"></i>
          {{ tooltipInfo.affectation || 'Inconnue' }}
        </div>
        <div>
          SHOW : {{ show }}<br>
          DISPLAYED : {{ displayed }}
        </div>
      </div>
    </div>
  </div>
  </transition>
</template>

<script>
import GlobalModel from "../models/GlobalModel.js";

let tempoHide = null;

export default {
  name: 'oscar-tooltip',
  props: {

  },
  data(){
    return {
      inside:false,
      displayed: false
    }
  },
  computed: {
    show(){
      return this.display || this.inside;
    },

    display(){
      if(!this.tooltipInfo ) {
        return true;
      }
      else {
        return this.tooltipInfo.display;
      }
    },
    x(){
      return this.tooltipInfo ? this.tooltipInfo.x +'px' : 0;
    },
    y(){
      return this.tooltipInfo ? this.tooltipInfo.y+'px' : 0;
    },
    tooltipInfo(){
      return GlobalModel.getters.tooltipInfos;
    }
  },
  methods: {
    handlerInside(){
      this.inside = true;
    },
    handlerOutside(){
      this.inside = false;
    }
  },
  watch: {
    // Système de delay pour cacher la tooltip
    show(){
      if( this.show ){
        this.displayed = true;
        clearTimeout(tempoHide);
      } else {
        tempoHide = setTimeout(()=>{
          this.displayed = false;
        }, 250);
      }
    }
  }
}
</script>

<style scoped>

.oscar-tooltip {
  position: absolute;
  background: rgba(0,0,0,.5);
  box-shadow: none;
  color: #fff;
  z-index: 9000;
  width: auto;
  height: auto;
  h3 {
    margin: 0;
    padding: 0 1em 0 0;
    display: flex;
    align-items: center;
    background: rgba(0,0,0,.75);
    img {
      height: 60px;
      width: 60px;
      display: inline-block;
    }
    .fullname {
      font-size: .8em;
      display: inline-block;
      line-height: 25px;
      .lastname {
        line-height: 35px;
        font-size: 1.5em;
        display: block;
      }
    }
  }
  .content {
    padding: .5em;
  }
}

</style>