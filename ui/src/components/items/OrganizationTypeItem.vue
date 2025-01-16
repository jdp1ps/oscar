<template>
    <article class="card card-xs" >

        <h3 class="card-title">
          <span class="">
            <code>[{{ organizationtype.id }}]</code>
            {{ organizationtype.label }}
            <span class="sup-info" :class="organizationtype.count ? 'primary' : 'neutral'">{{ organizationtype.id }}</span>
          </span>
          <nav class="text-right">
            <small>
              <a :href="'/organization?t[]='+organizationtype.id">Voir les organisations</a>
                <a href="#" @click.prevent="$emit('edit', organizationtype)"><i class="icon-floppy"></i> Modifier</a>
                <a href="#" @click.prevent="$emit('remove', organizationtype)"><i class="icon-trash"></i> Supprimer</a>
            </small>
          </nav>
        </h3>
      <p>{{ organizationtype.description }}</p>
      <section class="sub" v-if="organizationtype.children.length">
        <organization-type-item v-for="s in organizationtype.children"
                          @edit="$emit('edit', $event)"
                          @remove="$emit('remove', $event)"
                          :organizationtype="s"
                          :key="s.id"
        />
      </section>
    </article>
</template>
<script>
// import OrganizationTypeItem from '../components/items/OrganizationTypeItem.vue';
export default {
  components: {

  },
  props: {
    'creatable': { default: false },
    'organizationtype': { required: true }
  }
}
</script>