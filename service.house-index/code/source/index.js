const fetchMlsHouses = require('./mls');
const fetchListhubHouses = require('./listhub');

// 基本规则: 先获取mls, 再获取listhub
module.exports = async size => {
  return fetchListhubHouses(null, size);
};