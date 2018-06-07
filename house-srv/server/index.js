const net = require('net');
const config = requre('./config');
const HOST = '0';
const PORT = config.server.port;

var clients = {};
net.createServer(sock => {
  console.log('CONNECTED: ' + sock.remoteAddress + ':' + sock.remotePort);

  sock.setEncoding('utf8');
  sock.on('data', data => {
    console.log(data);
  });

  sock.on('close', data => {
      console.log('CLOSED: ' + sock.remoteAddress + ' ' + sock.remotePort);
  });

}).listen(PORT, HOST);

console.log('Server listening on ' + HOST +':'+ PORT);