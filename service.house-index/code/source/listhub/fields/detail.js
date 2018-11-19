const db = require('local/knex').db
const xml2js = require('node-xml2js-promise')
const vGet = require('local/object-get')
const extsFieldRules = require('./detail/field-exts')

module.exports = async (d, res) => {
  if (!res.prop_type) return {}

  let fieldRules = await db('listhub_house_field_prop_rule')
    .where('type_id', res.prop_type.toLowerCase())
    .first()
    .then(d => `<groups>${d.xml_rules}</groups>`)
    .then(xml2js)
    .then(d => d.groups.group)
    .then(mapToItems)
  
  return buildToResult(fieldRules)(d)
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

    fieldRules = Object.assign({}, fieldRules, extsFieldRules)

    let value
    for (let field in fieldRules) {
      let rule = fieldRules[field] instanceof Array ? fieldRules[field][0] : fieldRules[field]

      if (rule.hasOwnProperty('path')) {
        let path = fieldRules[field][0].path[0]
        path = path.replace(/\//g, '[0].') // xml path需转为json path
        if (!rule.hasOwnProperty('filter')) {
          path += '[0]' // 不带filter时才直接取值
        }

        if (value = vGet(source, path).val()) {
          if (rule.filter) {
            value = filterField(rule.filter[0], value)
          }
          results[field] = value
        }
      } else if(rule.hasOwnProperty('value')) {
        if (value = rule.value(source)) {
          results[field] = value
        }
      }
    }

    return results
  }
}

function filterField(type, value) {
  switch(type) {
    case 'count':
      return value.length
    case 'values':
      return value // 由于需要翻译，因此将原样数组形式返回
  }
}