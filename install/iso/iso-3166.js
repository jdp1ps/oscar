// Usage : node install/iso-3166.js iso-3166-final.js
// Description : génère un JSON propre avec les codes ISO "pompés" sur le site ISO
var fs = require('fs'),
    xml2js = require('xml2js');

var parser = new xml2js.Parser();
fs.readFile(__dirname + '/iso-3166-src.xml', function(err, data) {
    let output = [];
    parser.parseString(data, function (err, result) {
        let country = result.table.tbody[0].tr;
        country.forEach(function(tr){
            let en = tr.td[0].button[0]._.replace(/(\r\n|\n|\r)/gm, "").replace(/\s\s+/g, ' ').trim();
            let fr = tr.td[1]._.replace(/(\r\n|\n|\r)/gm, "").replace(/\s\s+/g, ' ').trim();
            let alpha2 = tr.td[2]._.replace(/(\r\n|\n|\r)/gm, "").replace(/\s\s+/g, ' ').trim();
            let alpha3 = tr.td[3]._.replace(/(\r\n|\n|\r)/gm, "").replace(/\s\s+/g, ' ').trim();
            let numeric = tr.td[4]._.replace(/(\r\n|\n|\r)/gm, "").replace(/\s\s+/g, ' ').trim();

            // Petit coup de propre
            fr = fr.replace(/^(.*) \((la |La|l'|le|la|les)?\)\*?$/gm, '$1');

            output.push({
                "en" : en,
                "fr" : fr,
                "alpha2" : alpha2,
                "alpha3" : alpha3,
                "numeric" : numeric
            })
        });
        console.log(JSON.stringify(output));
    });
});