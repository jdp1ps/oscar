<section>
    <nav class="oscar-sorter">
        <i class="icon-sort"></i>
        Trie :
        <a class="oscar-sorter-item" href="#" :class="{active: packerKey == 'enrolled' }" @click.prevent="changeStack('enrolled', 'role')">Par {{label}}</a> /
        <a class="oscar-sorter-item" href="#" :class="{active: packerKey == 'role' }" @click.prevent="changeStack('role', 'enrolled')">Par rôle</a>
    </nav>

    <strong v-for="enroll in packed" class="cartouche" v-bind:class="enroll.extraClass">
        {{ enroll.label }}
        <a href="#">
            <i class="icon-add"></i>
        </a>
        <span class="addon">
            <span v-for="plug in enroll.packed">
                <i v-if="plug.role.context == 'activity'" class="icon-cube"></i>
                <i v-if="plug.role.context == 'project'" class="icon-cubes"></i>
                {{ plug.label }}
                <nav v-if="plug.role.deletable || plug.role.editable">
                    <a @click.prevent="deleteRole(plug.role)" v-if="plug.role.deletable">
                        <i class="icon-trash"></i>
                    </a>
                    <a @click.prevent="editRole(plug.role)" v-if="plug.role.editable">
                        <i class="icon-pencil"></i>
                    </a>
                </nav>
            </span>
        </span>
    </strong>
</section>