import { mls as mlsBaseRules } from '../data'
import _ from 'lodash'
import { format, filter } from '../field-render'
import localizes from '../data/localizes.json'

const renderFieldMap = (rule, field, lang) => {
  let map = {}

  // 平级
  _.map(['title', 'suffix', 'suffix', 'format', 'map', 'filter'], key => {
    if (rule[key]) map[key] = rule[key]._text
  })

  // 语言配置
  if (rule['zh-CN']) {
    if (lang === 'zh-CN') {
      map['zh-CN'] = renderFieldMap(rule['zh-CN'], field, lang)
    }
  }

  // values配置
  if (rule.values) {
    map.values = _.mapValues(rule.values, (val) => {
      return val._text
    })
  }

  // 继承原始
  if (mlsBaseRules[field]) {
    map = _.merge({}, mlsBaseRules[field], map)
  }

  // 合并语言
  if (map['zh-CN']) {
    if (lang === 'zh-CN') {
      map = _.merge({}, map, map['zh-CN'])
    }
    delete map['zh-CN']
  }

  // 返回
  return map
}

/**
 * 获取详情
 */
export default (data, rules, referenceFields, ctx) => {
  const groups = rules.groups.group.map(group => {
    // 分组标题
    let title = group.title._text
    if (ctx.lang === 'zh-CN' && localizes[title]) {
      title = localizes[title]
    }

    // 字段项目s
    let items = _.mapValues(group.items, (rule, field) => {
      let opts = renderFieldMap(rule, field, ctx.lang)
      return getFieldValue(data, field, opts, referenceFields, ctx.lang)
    })

    // 清除空的字段
    let retItems = {}
    for (let field in items) {
      let rule = items[field]
      if (rule.value && rule.value !== '' && !rule.is_empty) {
        retItems[field] = rule
      }
    }

    return {
      title,
      items: retItems
    }
  })

  // 清除空的group, 即items.length为0
  return groups.filter(group => {
    return Object.keys(group.items).length > 0
  })
}

/**
 * 获取值
 */
function getFieldValue(data, field, opts, referenceFields, lang = 'zh-CN') {
  let entity = {}

  //获取真实值
  entity.value = data[field]

  // filter
  if (opts.filter) {
    entity.value = filter(opts.filter, entity.value, lang)
  }

  // map
  if (opts.map) {
    if (entity.value && entity.value.length > 0) {
      entity.value = entity.value.split(',').map(value => {
        if (referenceFields[field] && referenceFields[field][value]) {
          value = referenceFields[field][value]
          return 'zh-CN' && value[1] ? value[0] : value[0]
        }
      }).filter(value => {
        return value
      }).join(',')
    }
  }

  // format
  if (opts.format) {
    entity = _.merge({}, entity, format(opts.format, entity.value, lang))
  }

  // values
  if (opts.values) {
    if (opts.values[entity.value]) {
      entity.value = opts.values[entity.value]
    }
  }

  entity = _.merge({}, opts, entity)

  // 清除无用配置项
  _.forEach(['format', 'filter', 'map', 'values'], d => {
    if (entity[d]) delete entity[d]
  })

  return entity
}
