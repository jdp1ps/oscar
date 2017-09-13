var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);


var clients = [];

app.get('/', function(req, res){
    res.sendFile(__dirname + '/index.html');
});

io.on('connection', function(socket){
    var query = socket.handshake.query;
    if( !query.token ){
        console.log('Bad user');
    }
    else {
        console.log('a user connected', query.token);
        clients.push(query.token);
        socket.on('disconnect', () => {
            console.log(query.token, "leave...");
        })
    }
});

io.on('emit', function(){
   console.log("emit", arguments);
});


http.listen(3000, function(){
    console.log('listening on *:3000');
});