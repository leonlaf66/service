const knex = require('local/knex').db;
const cache = {};

async function load() {
  if (Object.keys(cache).length === 0) {
    await knex('town')
      .select('id', 'short_name', 'name')
      .where('state', 'MA')
      .then(rows => {
        rows.forEach(d => {
          cache[d.short_name] = {
            id: d.id,
            name: d.name,
            name_cn: d.name_cn
          };
          cache[d.id] = {
            name: d.name,
            name_cn: d.name_cn,
            short_name: d.short_name
          };
        });
      })
  }
  return cache;
};

module.exports = {
  load,
  find(id) {
    return cache[id];
  }
};