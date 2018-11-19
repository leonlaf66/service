const city = require('local/cities');
const subway = require('./house-subway');
const searchBuilder = require('./search-build');
const entityBuilder = require('./entity-build');

module.exports = async d => {
  await city.load(d.state);
  if (d.state === 'MA') {
    await subway.load();
  }

  d.$city = city.find(d.state, d.city_code);
  if (d.state === 'MA') {
    d.$subway = subway.find(d.latitude, d.longitude);
  }

  let searchData = await searchBuilder(d);
  d.$searchData = searchData;
  
  let entityIndex = entityBuilder(d);


  return entityIndex;
};