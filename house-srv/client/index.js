const net = require('net');
const config = requre('./config');
const client = new net.Socket();

/*
const { PGClient } = require('pg');
const pg = new PGClient({
  host: 'www.usleju.com',
  port: 5432,
  user: 'usleju',
  password: 'secretpassword!!',
  database: 'usleju'
});*/

client.setEncoding('utf8');

client.connect(config.server.port, config.server.host, function () {
  console.log('OK');
});