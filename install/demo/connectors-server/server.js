/*******************************
 * 
 * EXEMPLE de CONNECTOR BASIQUE
 *
 *******************************/


var http = require('http'),
    fs = require('fs');

// Configuration
var port = 8888;    


// Les données sont lues dans les fichiers JSON d'exemple
var Datas = {
    getPerson(uid){
        var person = null;
        Datas.getPersons().forEach(function(p){
            if( p.uid == uid ){
                person = p;
            }
        })
        return person;
    },
    getPersons(){
        var file = fs.readFileSync(__dirname +'/../persons.json');
        return JSON.parse(file);
    },
    getOrganization(uid){
        var organization = null;
        Datas.getOrganizations().forEach(function(p){
            if( p.uid == uid ){
                organization = p;
            }
        })
        return organization;
    },
    getOrganizations(){
        var file = fs.readFileSync(__dirname +'/../organizations.json');
        return JSON.parse(file);
    }
};


// Serveur qui fournis l'API
var server = http.createServer(function(req, res){

    var url = req.url,
        regPerson, regOrg,
        regexPerson = /\/persons\/(.*)/,
        regexOrganization = /\/organizations\/(.*)/,
        json,
        code = 200
    ;

    
    // La réponse est au format JSON (UTF8)
    res.setHeader('Content-Type', 'application/json; charset=utf-8');


    // Toutes les personnes ~ /persons
    if( url == '/persons' || url == '/persons/' ) {
        json = Datas.getPersons();
    }

    // Personne ~ /persons/UID
    else if (regPerson = regexPerson.exec(url) ) {
        var uid = regPerson[1];
        var person = Datas.getPerson(uid);
        if (person) {
            json = person;
        } else {
            code = 404;
            json = { "error" : "Aucune personne trouvée pour l'UID " + uid };
        }
    }

    // Toutes les organisations ~ /organizations
    else if( url == '/organizations' || url == '/organizations/' ) {
        json = Datas.getOrganizations();
    }

    // Organisation ~ /organizations/UID
    else if (regOrg = regexOrganization.exec(url) ) {
        var uid = regOrg[1];
        var org = Datas.getOrganization(uid);
        if( org ){
            json = org;
        } else {
            code = 404;
            json = { "error" : "Aucune organisation trouvée pour l'UID " + uid };
        }
    } 

    else {
        code = 400;
        json = {"error" : "Mauvaise utilisation de l'API"};
    }

    console.log(new Date().toISOString(),"GET", code, url);
    res.writeHead(code);
    res.end(JSON.stringify(json));
});

server.listen(port, function(){
    console.log('COD (Connector Oscar de Démonstration) démarré sur le port ' + port)
});