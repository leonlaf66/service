const net = require('net');
const { Connection } = require('socket-json-wrapper')
const config = require('./config');
const HOST = '0';
const PORT = config.server.port;

var clients = {};
net.createServer(sock => {
  sock.setEncoding('utf8');

  const connection = new Connection(sock);
  connection.on('message', data => {
    try {
      // 解析数据
      data = JSON.parse(data);
      
      // 反馈
      connection.send({
        id: data.id,
        status: true
      });

      //打印输出
      console.log(data.id);
    } catch (e) {
      console.log('Error: ' + data);
    }
  });
}).listen(PORT, HOST);

console.log('Server listening on ' + HOST +':'+ PORT);