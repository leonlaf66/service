const knex = require('local/knex').db;
const cache = {};

const mapToCities = (state, items) => {
  items.forEach(d => {
    if (state === 'MA') { // mls特有
      cache[state][d.short_name] = {
        id: d.id,
        name: d.name,
        name_zh: d.name_zh
      };
    } else { // listhub特有
      cache[state][d.name.toLowerCase()] = {
        id: d.id,
        name: d.name,
        name_zh: d.name_zh
      };
    }
    // mls + listhub共有
    cache[state][d.id] = {
      id: d.id,
      name: d.name,
      name_zh: d.name_zh
    };
  });
};

async function load(state) {
  if (cache.hasOwnProperty(state)) { // 已经load，跳过
    return;
  } else {
    cache[state] = {};
  }

  if (Object.keys(cache[state]).length === 0) {
    await knex('city_' + state.toLowerCase())
        .select('*')
        .then(items => mapToCities(state, items));
  }

  return cache[state];
};

module.exports = {
  load,
  find(state, id) {
    return cache[state][id];
  }
};