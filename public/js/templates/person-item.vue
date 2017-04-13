<strong :class="hasLink ? 'link' : 'plain'" @click="navigateToPerson" @mouseover="tooltip">
    <i class="icon-user"></i>
    {{person ? person.displayname : "Anonymous"}}
</strong>
