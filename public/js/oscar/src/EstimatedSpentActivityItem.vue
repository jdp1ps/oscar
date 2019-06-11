<template>
<!--

 if( count($line['children']) > 0 ){
        $out = "<tr class='level-$level'>";
        $out .= "<th class='heading'>";
        $out .= $line['label'];
        $out .= "</th>";
        foreach ($years as $year) {
            $out .= "<th> &nbsp;</th>";
        }
        $out .= "<th>TOTAL</th>";
        $out .= "</tr>";

        foreach ($line['children'] as $l) {
            $out .= renderLine($l, $years, $level+1);
        }


    } else {
        $out = "<tr class='computed'>";
        $out .= "<th class='heading'>";
        $out .= $line['label'];
        $out .= "</th>";
        foreach ($years as $year) {
            $name = "spent[$year][$id]";
            $out .= "<td><input type='text' name='$name' placeholder='0.0' /></td>";
        }
        $out .= "<td>0.0</td>";
        $out .= "</tr>";

    }

-->


            <tr v-if="type.children.length == 0">
                <th>{{ type.label }}</th>
                <td v-for="year in years">{{ year }}</td>
            </tr>

            <tr v-else>
                <th>{{ type.label }}</th>
                <td :colspan="years.length">---</td>
            </tr>




</template>
<script>
    // nodejs node_modules/.bin/poi watch --format umd --moduleName  EstimatedSpentActivityItem --filename.css EstimatedSpentActivityItem.css --filename.js EstimatedSpentActivityItem.js --dist public/js/oscar/dist public/js/oscar/src/EstimatedSpentActivityItem.vue

    export default {
        props: {
            type: { required: true },
            years: { required: true }
        },

        data(){
            return {

            }
        },

        methods:{

        }
    }
</script>