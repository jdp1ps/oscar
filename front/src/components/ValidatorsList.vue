<template>
  <section class="validators-list">
    <div v-if="fixed.length == 0" class="alert alert-warning">
      <div v-if="inherits.length == 0" class="alert alert-danger">
        <i class="icon-attention-1"></i>
        Personne n'a été trouvé pour la validation, les déclarations ne seront jamais validées
      </div>
      <div v-else>
        <p>
          <i class="icon-attention-1"></i>
          Aucun validateur désignés. Les personnes suivantes seront sollicitées automatiquement :
        </p>
        <ul>
          <li v-for="v in inherits">
            <strong>
              <i class="icon-user"></i>
              {{ v.person }}
            </strong>
          </li>
        </ul>
      </div>
    </div>
    <section class="persons" v-else>
      <article class="personcard card" v-for="p in fixed">
        <h5 class="personcard-header">
          <img :src="'//www.gravatar.com/avatar/' + p.mailMd5 +'?s=40'" alt="" class="personcard-gravatar">
          <div class="personcard-infos">
            <strong>{{ p.person }}</strong><br>
            <small>
              <i class="icon-mail"></i>
              {{ p.mail }}
            </small>
          </div>
        </h5>
        <nav class="buttons text-center">
          <button class="btn btn-danger btn-xs xs" @click="$emit('removeperson', { person_id: p.person_id, level: level })">
            <i class="icon-trash"></i>
            Supprimer
          </button>
        </nav>
      </article>
    </section>
    <button @click="$emit('addperson', level)" class="btn btn-primary">
      <i class="icon-user"></i>
      Ajouter
    </button>
  </section>
</template>

<script>
export default {
  props: {
    fixed: {
      type: Array,
      required: true
    },
    inherits: {
      type: Array,
      required: true
    },
    level: {
      type: String,
      required: true
    }
  }
}
</script>