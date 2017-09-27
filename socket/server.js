var express = require('express');
var fs = require('fs');
var https = require('https');
var pg = require('pg');
var bodyParser = require('body-parser');
var config = require('./config.json');

var app = express();
var server = https.createServer({
	key: fs.readFileSync(config.ssl.key),
	cert: fs.readFileSync(config.ssl.cert)
}, app);
var io = require('socket.io')(server);

app.use( bodyParser.json() );       // to support JSON-encoded bodies
app.use(bodyParser.urlencoded({     // to support URL-encoded bodies
    extended: true
}));

var clients = [];
var clientsOnline = [];

app.get('*', function(req, res, next){
	console.log("GET", (new Date()).toISOString(), req.connection.remoteAddress, req.url);
	next();
});

// Page d'accueil
app.get('/', function(req, res){
    res.sendFile(__dirname + '/index.html');
});

app.post(config.socket.push_path, function(req, res){
    var ids = req.body.ids.split(',');
    console.log('PUSH with', ids);
    var client = new pg.Client({
        user: config.bdd.user,
        host: config.bdd.host,
        database: config.bdd.base,
        password: config.bdd.pass
    });
    client.connect();
    client.query('SELECT * FROM notification WHERE id IN(' +ids.join(',') + ')', function(err, result){
        if( err ){
            console.log("ERROR", err);
        }
        if( result ) {
            for( var i=0; i<result.rows.length; i++ ){
                sendNotification(result.rows[i]);
            }
        }
        client.end();
    })
    res.end();
});

function sendNotification( datas ){
     for( var i=0; i<clients.length; i++ ){
         if( clients[i].personid == datas.recipientid){
            console.log("ENVOI à [", clients[i].personid, "] ",clients[i].username);
            clients[i].emit('notification', { "notification": datas });
        }
    }
}

function addClient( socket){
    console.log("Connexion", socket.username, socket.personid);
    clients.push(socket);
    clientsOnline.push(socket.personid);
}

function removeClient( socket ){
    console.log("Déconnection", socket.username, socket.personid);
    var index = clients.indexOf(socket);
    if( index >= 0 ){
        clients.splice(index, 1);
    }
    index = clientsOnline.indexOf(socket.personid);
    if( index >= 0 ){
        clientsOnline.splice(index, 1);
    }
}

function displayClientsList(){
    for( var i=0; i<clients.length; i++ ){
        console.log(clients[i].username, "\t", clients[i].usertoken);
        clients[i].emit('event', { 'foo': "bar", 'connected': clientsOnline});
    }
}


io.on('connection', function(socket){
    var query = socket.handshake.query;
    if( !query.token ){
        console.log('Bad user');
    }
    else {
        var client = new pg.Client({
            user: config.bdd.user,
            host: config.bdd.host,
            database: config.bdd.base,
            password: config.bdd.pass
        });

        client.connect();

        client.query('SELECT display_name, p.id as id FROM authentification a LEFT JOIN person p ON a.username = p.ladaplogin WHERE a.secret = $1 ', [query.token], function(err, res){
            if( res.rows.length == 1 ){
                var displayName = res.rows[0].display_name;
                socket.username = displayName;
                socket.usertoken = query.token;
                socket.personid = res.rows[0].id;
                if( res.rows[0].id ){
                    connected = socket.personid;
                }
                addClient(socket);
            }
            client.end();
        });

        socket.on('disconnect', () => {
            removeClient(socket);
        });
    }
});


server.listen(config.socket.port, function(){
    console.log('listening on *:', config.socket.port);
});
