var express = require('express');
var fs = require('fs');
var config = require('./config.json');
var app = express();
var exec = require('child_process').exec;

/*
exec('php ../public/index.php oscar auth:info bohr', function(err, stdout, stderr) {
  console.log(stdout)
})
*/

var clients = [];
var clientsOnline = [];
var bodyParser = require('body-parser');



app.use( bodyParser.json() );       // to support JSON-encoded bodies
app.use(bodyParser.urlencoded({     // to support URL-encoded bodies
    extended: true
}));

app.get('*', function(req, res, next){
    console.log("GET", (new Date()).toISOString(), req.connection.remoteAddress, req.url);
    next();
});



var  https, server;

if( config.ssl === true  ){
    https = require('https');
    server = https.createServer({
        key: fs.readFileSync(config.ssl.key),
        cert: fs.readFileSync(config.ssl.cert)
    }, app)
}

else {
    https = require('http');
    server = https.createServer(app);

}

var io = require('socket.io')(server);

// Page d'accueil
app.get('/', function(req, res){
    res.sendFile(__dirname + '/index.html');
});

app.post(config.socket.push_path, function(req, res){

    // IDS des notifs
    console.log(req.body.ids);
    var ids = req.body.ids.split(",");
    console.log("Notifications à diffuser", ids);
    for( var i=0; i<ids.length; i++ ){
        sendNotification(ids[i]);
    }
/*
    exec('php ../public/index.php oscar json:notifications '+ids, function(err, stdout, stderr) {
        if( err  ){
            console.log("ERROR", err);
        }
        if( stdout ){
            console.log('OSCAR envoi', stdout);
            var result = JSON.parse(stdout);
            for( var i=0; i<result.length; i++ ){
                sendNotification(result[i]);
            }

        }
    })
    */

    res.end();
});

function sendNotification( datas ){
    console.log("sendNotification", datas);
     for( var i=0; i<clients.length; i++ ){
         if( clients[i].usertoken == datas){
            clients[i].emit('notification', {});
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
    console.log("CONNECTION");
    var query = socket.handshake.query;
    if( !query.token ){
        console.log('Bad user');
    }
    else {
        console.log("GET INFOS USER");
        // CHECK SECRET
        exec('php ../public/index.php oscar json:user '+query.token, function(err, stdout, stderr) {
            if( stdout ){
                var data = JSON.parse(stdout);
                socket.username = data.fullname;
                socket.usertoken = query.token;
                socket.personid = data.id;
                addClient(socket);
                connected = socket.personid;
            }
        })

        socket.on('disconnect', () => {
            removeClient(socket);
        });
    }
});


server.listen(config.socket.port, function(){
    console.log('listening on *:', config.socket.port);
});
