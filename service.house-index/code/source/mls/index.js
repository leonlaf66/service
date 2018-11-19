const knex = require('local/knex').db_source;
const rules = require('./rules');

async function mapTo(sourceRows) {
  let resultItems = [];

  for (let i in sourceRows) {
    let sourceRow = sourceRows[i];
    let targetRow = {};

    for (let field in rules) {
      let result = (rules[field])(sourceRow);
      if (result && typeof result === 'object' && result.constructor.name === 'Promise') {
        result = await result;
      }
      targetRow[field] = result;
    }

    resultItems.push(targetRow);
  }
  return resultItems;
}

module.exports = (lastAt, size) => {
  return knex('mls_rets')
    .limit(size)
    .then(mapTo);
};