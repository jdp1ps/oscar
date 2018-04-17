<template>
    <article class="card card-xs" >
        <h3 class="card-title">
            {{ organizationtype.label }}
            <small>
                <a href="#" @click.prevent="$emit('edit', organizationtype)"><i class="icon-floppy"></i> Modifier</a>
                <a href="#" @click.prevent="$emit('remove', organizationtype)"><i class="icon-trash"></i> Supprimer</a>
            </small>
        </h3>
        <section class="sub" v-if="organizationtype.children.length">
            <organizationtype v-for="s in organizationtype.children"
                    @edit="$emit('edit', $event)"
                    @remove="$emit('remove', $event)"
                    :organizationtype="s"
                    :key="s.id"
            />
        </section>

    </article>
</template>
<script>
    let OrganizationTypeItem = {
        props: ['organizationtype', 'creatable'],
        components: {},

    };

    OrganizationTypeItem.components.organizationtype = OrganizationTypeItem;

    export default OrganizationTypeItem;
</script>