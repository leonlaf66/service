const knex = require('knex');
const config = require('config');

module.exports = {
  db: knex(config.db),
  db_source: knex(config.db_source)
};