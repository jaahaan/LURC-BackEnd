// const express= require('express');
// const app = express();
// const http = require('http');
// const expressServer = http.createServer(app);

// const {Server} =require('socket.io');
// const io = new Server(expressServer);

// io.on('connection', function(socket){
//     console.log("User Connected")
//     socket.on('chat', function(msg){
//         io.emit('chat_transfer', msg)
//     })
//     socket.on('notification', function(notification){
//         io.emit('get_notification', notification)
//     })
// });
// app.get('/', function(req, res){
//     res.sendFile(__dirname+"/index.html")
// });
// expressServer.listen(5000, function(){
//     console.log("Server Run @ 5000")
// });

const express = require('express');

const app = express();


const server = require('http').createServer(app);


const io = require('socket.io')(server, {
    cors: { origin: "*"}
});


io.on('connection', (socket) => {
    console.log('connection');

    socket.on('sendChatToServer', (message) => {
        console.log(message);

        io.sockets.emit('sendChatToClient', message);
        // socket.broadcast.emit('sendChatToClient', message);
    });
    socket.on('notification', function(notification){
        socket.broadcast.emit('get_notification', notification)
    })

    socket.on('disconnect', (socket) => {
        console.log('Disconnect');
    });
});

server.listen(5000, () => {
    console.log('Server is running');
});