const fetchSource = require('./code/source');
const houseIndex = require('./code/convert');

(async () => {
  let items = await fetchSource(10);

  let res = await houseIndex(items[3]);
  console.log(res);

  process.exit();
})();
