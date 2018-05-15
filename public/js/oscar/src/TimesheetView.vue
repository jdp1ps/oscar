<template>
    <div class="timesheet"><h1> <i class="icon-file-excel"></i>Feuille de temps</h1>
        <p class="help-block">Seul les déclarations <strong>validées</strong> sont affichées ici.</p>
        <section v-for="activityDatas in structuredDatas">
            <h2>
                <i class="icon-cube"></i>
                Déclarations validées pour <strong>{{ activityDatas.label }}</strong>
            </h2>
            <section v-for="personDatas in activityDatas.persons">
                <table class="table table-bordered table-timesheet">
                    <thead>
                    <tr>
                        <th>{{ personDatas.label }}</th>
                        <th v-for="w in activityDatas.wps">{{ w }}</th>
                        <th class="time">Commentaire(s)</th>
                        <th class="time">Total</th>
                    </tr>
                    </thead>
                    <tbody v-for="monthDatas, month in personDatas.months" class="person-tbody">
                    <tr class="header-month">
                        <th :colspan="monthDatas.wps.length + 3">{{ month }}</th>
                    </tr>
                    <tr v-for="dayDatas, day in monthDatas.days" class="data-day">
                        <th>{{ day }}</th>
                        <td v-for="tpsDay in dayDatas.wps" class="time">{{tpsDay}}</td>
                        <td class="timesheet-comment">{{ dayDatas.comments }}</td>
                        <th class="time">{{ dayDatas.total }}</th>
                    </tr>
                    <tr class="subtotal">
                        <th>&nbsp;</th>
                        <td v-for="tps in monthDatas.wps"  class="time">{{tps}}</td>
                        <td>&nbsp;</td>
                        <th class="time">{{ monthDatas.total }}</th>
                    </tr>
                    </tbody>
                    <tfoot class="person-tfoot">
                    <tr>
                        <th>Total</th>
                        <th v-for="totalWP in personDatas.totalWP" class="time">{{totalWP}}</th>
                        <td>&nbsp;</td>
                        <th class="time">{{ personDatas.total }}</th>
                    </tr>
                    </tfoot>
                </table>
                <nav class="text-right">
                    <a :href="getBase64CSV(personDatas)" :download="'Feuille-de-temps' + personDatas.label + '.csv'" class="btn btn-primary btn-xs">
                        <i class="icon-download-outline"></i>
                        Télécharger le CSV
                    </a>
                </nav>
            </section>
        </section>
    </div><div class="timesheet"><h1> <i class="icon-file-excel"></i>Feuille de temps</h1>
    <p class="help-block">Seul les déclarations <strong>validées</strong> sont affichées ici.</p>
    <section v-for="activityDatas in structuredDatas">
        <h2>
            <i class="icon-cube"></i>
            Déclarations validées pour <strong>{{ activityDatas.label }}</strong>
        </h2>
        <section v-for="personDatas in activityDatas.persons">
            <table class="table table-bordered table-timesheet">
                <thead>
                <tr>
                    <th>{{ personDatas.label }}</th>
                    <th v-for="w in activityDatas.wps">{{ w }}</th>
                    <th class="time">Commentaire(s)</th>
                    <th class="time">Total</th>
                </tr>
                </thead>
                <tbody v-for="monthDatas, month in personDatas.months" class="person-tbody">
                <tr class="header-month">
                    <th :colspan="monthDatas.wps.length + 3">{{ month }}</th>
                </tr>
                <tr v-for="dayDatas, day in monthDatas.days" class="data-day">
                    <th>{{ day }}</th>
                    <td v-for="tpsDay in dayDatas.wps" class="time">{{tpsDay}}</td>
                    <td class="timesheet-comment">{{ dayDatas.comments }}</td>
                    <th class="time">{{ dayDatas.total }}</th>
                </tr>
                <tr class="subtotal">
                    <th>&nbsp;</th>
                    <td v-for="tps in monthDatas.wps"  class="time">{{tps}}</td>
                    <td>&nbsp;</td>
                    <th class="time">{{ monthDatas.total }}</th>
                </tr>
                </tbody>
                <tfoot class="person-tfoot">
                <tr>
                    <th>Total</th>
                    <th v-for="totalWP in personDatas.totalWP" class="time">{{totalWP}}</th>
                    <td>&nbsp;</td>
                    <th class="time">{{ personDatas.total }}</th>
                </tr>
                </tfoot>
            </table>
            <nav class="text-right">
                <a :href="getBase64CSV(personDatas)" :download="'Feuille-de-temps' + personDatas.label + '.csv'" class="btn btn-primary btn-xs">
                    <i class="icon-download-outline"></i>
                    Télécharger le CSV
                </a>
            </nav>
        </section>
    </section>
</div>
</template>
<script>
    export default {
        props: ['withOwner'],
        data(){
            return store
        },
        computed: {
            colspan(){
                return this.workPackageIndex.length;
            },
            structuredDatas(){
                return store.timesheetDatas();
            }
        },

        methods: {
            getBase64CSV(datas){
                var csv = [];
                let header = [datas.label].concat(datas.wps).concat(['comentaires','total']);
                csv.push(header);
                for (var month in datas.months) {
                    if( datas.months.hasOwnProperty(month) ){
                        let monthData = datas.months[month];

                        for (var day in monthData.days) {
                            if( monthData.days.hasOwnProperty(day) ){
                                let dayData = monthData.days[day];
                                let line = [day];
                                dayData.wps.forEach((dayTotal) => {
                                    line.push(dayTotal.toString().replace('.', ','));
                                });
                                line.push(dayData.comments);
                                line.push(dayData.total.toString().replace('.', ','));
                                csv.push(line);
                            }
                        }

                        let monthLine = ['TOTAL pour ' +month];
                        monthData.wps.forEach((monthTotal) => {
                            monthLine.push(monthTotal.toString().replace('.', ','));
                        });
                        monthLine.push('');
                        monthLine.push(monthData.total.toString().replace('.', ','));
                        csv.push(monthLine);

                    }
                }

                let finalLine = ["TOTAL"];
                datas.totalWP.forEach((totalCol) => {
                    finalLine.push(totalCol.toString().replace('.', ','));
                });
                finalLine.push('');
                finalLine.push(datas.total.toString().replace('.',','));
                csv.push(finalLine);

                var str = Papa.unparse({
                    data: csv,
                    quotes: true,
                    delimiter: ",",
                    newline: "\r\n"
                });

                return 'data:application/octet-stream;base64,' + btoa(unescape(encodeURIComponent(str)));
            }
        }
    }
</script>