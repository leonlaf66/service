const net = require('net');
const config = require('./config');
const client = new net.Socket();

client.setEncoding('utf8');

module.exports = new Promise(function (resolve, reject) {
  client.connect(config.server.port, config.server.host, function () {
    console.log('OK');
    resolve(client);
  });
});