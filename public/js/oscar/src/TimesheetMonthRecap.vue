<template>
    <section class="timesheetmonth-recap">
        <h1>Mois de {{ mois }}</h1>
    </section>
</template>
<script>
    var moment = require('moment');
    export default {
        computed: {
            mois(){
                return "todo";
            }
        }
    }
</script>