const net = require('net');
const config = require('./config');
const HOST = '0';
const PORT = config.server.port;

var clients = {};
net.createServer(sock => {
  sock.setEncoding('utf8');
  sock.on('data', data => {
    data = JSON.parse(data);
    sock.write(JSON.stringify({
      id: data.id,
      status: true
    }) + "\n");
    console.log(data.id);
  });

}).listen(PORT, HOST);

console.log('Server listening on ' + HOST +':'+ PORT);