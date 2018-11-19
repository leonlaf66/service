const rules = require('./rules');

module.exports = d => {
  const result = {};

  Object.keys(rules).forEach(field => {
    let call = rules[field];
    result[field] = call(d, result);
  })
  return result;
};