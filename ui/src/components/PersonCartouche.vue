<template>
  <span class="person-text">
    {{ person.label }}
    <div class="info-bulle">
      <header>
        <figure>
          <img :src="'https://www.gravatar.com/avatar/' + person.mailMd5 + '?s=60'" alt="">
        </figure>
        <div class="infos">
          <div class="firstname">
            {{ person.firstName }}
          </div>
          <div class="lastname">
            {{ person.lastName }}
          </div>
        </div>
      </header>
      <div class="content">
        <div style="white-space: nowrap">
          <i class="icon-mail"></i>
          <span v-if="person.email">{{ person.email }}</span>
          <em v-else>Inconnu</em>
        </div>
        <div><i class="icon-phone-outline"></i>
          <span v-if="person.phone">{{ person.phone }}</span>
          <em v-else>Inconnu</em>
        </div>
        <div>
          <i class="icon-building-filled"></i>
          <span v-if="person.affectation">{{ person.affectation }}</span>
          <em v-else>Inconnu</em>
        </div>
        <div v-if="person.ucbnSiteLocalisation">
          <i class="icon-direction"></i>
          <span>{{ person.ucbnSiteLocalisation }}</span>
        </div>
      </div>
      <footer>
        <a :href="person.url" class="btn btn-primary btn-xs" v-if="person.url">
          <i class="icon-zoom-in-outline"></i>
          Fiche</a>
        <code v-else>
          {{ person.id }}
        </code>
      </footer>
    </div>
  </span>
</template>
<script>
export default {
  name: 'PersonCartouche',
  props: {
    person: {required: true, type: Object}
  }
}
</script>
<style scoped>
.person-text {
  display: inline-block;
  position: relative;

  .info-bulle {
    display: none;
  }

  &:hover {
    .info-bulle {
      display: block;
    }
  }
}

.info-bulle {
  position: absolute;
  text-shadow: none;
  background-color: rgba(0, 0, 0, .7);
  box-shadow: -4px 4px 8px rgba(0, 0, 0, 0.3);

  header {
    background: #222;
    display: flex;

    .infos {
      font-size: 1.25em;
      padding: 4px .5em 4px .5em;
      font-weight: 700;

      .firstname {
        line-height: 16px;
        font-size: 16px;
        color: rgba(255, 255, 255, .8);
        font-style: italic;
      }

      .lastname {
        color: #fff;
        line-height: 36px;
        font-size: 30px;
      }
    }
  }

  .content {
    i {
      color: #999;
    }
    font-size: .9em;
    background: #fff;
    color: #111;
    padding: 0.3em 1em;

    > div {
      border-bottom: dotted 1px #777;

      &:last-child {
        border-bottom: none;
      }
    }
  }

  footer {
    font-size: .7em;
    padding: 0 .25em;
    border-top: 1px solid #DDD;
    background-color: #EEE;
    color: #555;
    text-align: right;
  }
}
</style>