const net = require('net');
const config = requre('./config');
const HOST = '0';
const PORT = config.server.port;

var clients = {};
net.createServer(function (socket) {
  socket.setEncoding('utf8');
  socket.on('data', function (data) {
    console.log(data);
  });
}).listen(PORT, HOST);

console.log('Server listening on ' + HOST +':'+ PORT);