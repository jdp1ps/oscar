<template>
  <transition name="fade">
    <div class="overlay" v-if="traces.length">
      <div class="overlay-content">
        <div class="overlay-title">
          <i class="icon-signal"></i>
          LOGS
          <div class="overlay-closer" @click="traces = []">
            x
          </div>
        </div>
        <section class="activities timeline">
          <span v-html="$filters.log('foo')"></span>
          <article class="timeline-item" :class="'type-'+item.type" v-for="item in traces">
            <time datetime="">
              <strong>{{ $filters.timeAgo(item.dateCreated.date) }}</strong>
              <div class="duration">{{ $filters.date(item.dateCreated.date) }}</div>
            </time>

            <div class="content" v-html="$filters.log(item.message)"></div>
          </article>
        </section>
      </div>
    </div>
  </transition>

  <button @click="fetch" class="btn btn-primary">
    <i class="icon-signal"></i>
    Afficher les logs
  </button>
</template>
<script>
import axios from "axios";
export default {
  name: 'ActivityLogs',
  props: {
    url: { required: true }
  },
  data() {
    return {
      traces: []
    }
  },
  methods: {
    fetch(){
      axios.get(this.url).then(response => {
        this.traces = response.data.traces;
      })
    }
  }
}
</script>