const net = require('net');
const config = require('./config');
const HOST = '0';
const PORT = config.server.port;

var clients = {};
net.createServer(sock => {
  sock.setEncoding('utf8');
  sock.on('data', data => {
    try {
      // 解析数据
      data = JSON.parse(data);
      // 反馈
      sock.write(JSON.stringify({
        id: data.id,
        status: true
      }) + "\n");
      //打印输出
      console.log(data.id);
    } catch (e) {
      console.log('Error: ' + data);
    }
  });

}).listen(PORT, HOST);

console.log('Server listening on ' + HOST +':'+ PORT);