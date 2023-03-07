const express = require("express");

const dotenv = require("dotenv");

const path = require("path");

dotenv.config();
const app = express();

app.use(express.json()); // to accept json data

const PORT = process.env.PORT;

const server = app.listen(
  PORT,
  console.log(`Server running on PORT ${PORT}...`)
);

const io = require("socket.io")(server, {
  pingTimeout: 60000,
  cors: {
    origin: "http://localhost:3000",
    // credentials: true,
  },
});

io.on("connection", (socket) => {
  console.log("Connected to socket.io");
  socket.on("setup", (userData) => {
    socket.join(userData.id);
    socket.emit("connected");
  });

  socket.on("join chat", (room) => {
    socket.join(room);
    console.log("User Joined Room: " + room);
  });
  socket.on("typing", (room) => socket.in(room).emit("typing"));
  socket.on("stop typing", (room) => socket.in(room).emit("stop typing"));

  socket.on("new message", (newMessageRecieved) => {
    var chat = newMessageRecieved.chat;

    // if (!chat.users) return console.log("chat.users not defined");

    // chat.users.forEach((user) => {
      socket.in(newMessageRecieved.to_id).emit("message recieved", newMessageRecieved);

      if (chat.from_id == newMessageRecieved.from_id) return;
      if (chat.to_id == newMessageRecieved.from_id) return;

      
    // });
    // if (newMessageRecieved) return console.log("chat.users not defined");
    // socket.in(newMessageRecieved.room_id).emit("message recieved", newMessageRecieved);

  });
  socket.on('notification', function(notification){
      socket.broadcast.emit('get_notification', notification)
  })
  socket.off("setup", () => {
    console.log("USER DISCONNECTED");
    socket.leave(userData.id);
  });
});

// const io = require('socket.io')(server, {
//     cors: { origin: "*"}
// });


// io.on('connection', (socket) => {
//     console.log('connection');
//     socket.on("join chat", (room) => {
//         socket.join(room);
//         console.log("User Joined Room: " + room);
//       });
//     socket.on('sendChatToServer', (message) => {
//         console.log(message);

//         io.sockets.emit('sendChatToClient', message);
//         // socket.broadcast.emit('sendChatToClient', message);
//     });
//     socket.on('chatNotificationToServer', (data) => {
//         console.log(data);

//         // io.sockets.emit('chatNotificationToClient', data);
//         socket.broadcast.emit('chatNotificationToClient', data);
//     });
//     socket.on('notification', function(notification){
//         socket.broadcast.emit('get_notification', notification)
//     })

//     socket.on('disconnect', (socket) => {
//         console.log('Disconnect');
//     });
// });

// server.listen(5000, () => {
//     console.log('Server is running');
// });