const serverConnector = require('./serverConnector');
const house = require('./house');

serverConnector.then(client => {
  house(data => {
    client.emit(data);
  });
});