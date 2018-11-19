const rules = require('./rules');

module.exports = async d => {
  let targetResult = {};
  let targetZhResult = {};

  for (let field in rules) {
    let result = (rules[field])(d, targetResult, targetZhResult);

    if (result && typeof result === 'object' && result.constructor.name === 'Promise') {
      result = await result;
    }

    targetResult[field] = result;
  }

  return targetResult;
};