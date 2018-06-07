const net = require('net');
const config = require('./config');
const HOST = '0';
const PORT = config.server.port;

var clients = {};
net.createServer(sock => {
  console.log('CONNECTED: ' + sock.remoteAddress + ':' + sock.remotePort);

  sock.setEncoding('utf8');
  sock.on('data', data => {
    data = JSON.parse(data);
    sock.write(JSON.stringify({
      id: data.id,
      status: true
    });
    console.log(data.id);
  });

  sock.on('close', data => {
      console.log('CLOSED: ' + sock.remoteAddress + ' ' + sock.remotePort);
  });

}).listen(PORT, HOST);

console.log('Server listening on ' + HOST +':'+ PORT);