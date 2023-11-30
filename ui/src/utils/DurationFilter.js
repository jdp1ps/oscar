export default {
    percent(value){
        return ''+(Math.round(value*10)/10).toFixed(1);
    },
    round1(value){
        return value.toFixed(1);
    },
    formatDuration(heure) {
        var h = Math.floor(heure);
        var m = Math.round((heure - h) * 60);
        return h + ':' + (m < 10 ? '0' + m : m);
    }
};
