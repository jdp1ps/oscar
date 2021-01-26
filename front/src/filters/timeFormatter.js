import moment from "moment";

function dateFull(date){
    var m = moment(date);
    return "le " + m.format('dddd D MMMM YYYY') + ', ' + moment(date).fromNow();
}

function dateFullSort(date){
    var m = moment(date);
    return m.format('D MMMM YYYY') + ', ' + moment(date).fromNow();
}

// Vue.filter('filesize', function(octets) {
//     var sizes = ['Octets', 'KB', 'MB', 'GB', 'TB'];
//     if (octets == 0) return '0 Octet';
//     var i = parseInt(Math.floor(Math.log(octets) / Math.log(1024)));
//     return Math.round(octets / Math.pow(1024, i), 2) + ' ' + sizes[i];
// });