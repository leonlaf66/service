const db = require('local/knex').db;
const xml2js = require('node-xml2js-promise');


module.exports = async d => {
  let fieldRules = await db('house_field_prop_rule')
    .where('type_id', d.prop_type.toLowerCase())
    .first()
    .then(d => `<groups>${d.xml_rules}</groups>`)
    .then(xml2js)
    .then(d => d.groups.group)
    .then(mapToItems);

  return buildToResult(fieldRules)(d);
}

function mapToItems(groups) {
    let rules = {}

    groups.forEach(group => {
      if (group.items instanceof Array) {
        group.items = group.items[0]
      }
      for (let field in group.items) {
        rules[field] = group.items[field]
      }
    })

    return rules
}

function buildToResult(fieldRules) {
  return source => {
    let results = {}
    let value

    for (let field in fieldRules) {
      value = typeof source[field] !== 'undefined' ? source[field] : null
      if (value) {
        results[field] = value
      }
    }

    return results
  }
};