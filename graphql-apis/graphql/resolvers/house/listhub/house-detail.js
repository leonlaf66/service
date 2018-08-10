import { select as xpath } from 'xpath'
import { format, filter } from '../field-render'
import { listhub as baseRules } from '../data'
import _ from 'lodash'

const renderFieldMap = (rule, field, lang) => {
  let map = {}

  // 平级
  _.map(['path', 'title', 'suffix', 'suffix', 'format', 'filter'], key => {
    if (rule[key]) map[key] = rule[key]._text
  })

  // 语言配置
  if (rule['zh-CN'] && lang === 'zh-CN') {
     map['zh-CN'] = renderFieldMap(rule['zh-CN'], field, lang)
  }

  // values配置
  if (rule.values) {
    map.values = _.mapValues(rule.values, (val) => {
      return val._text
    })
  }

  // 继承原始
  if (baseRules[field]) {
    map = _.merge({}, baseRules[field], map)
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
export default (data, rules, ctx) => {
  const groups = rules.groups.group.map(group => {
    // 分组标题
    const title = ctx.tt(group.title._text, group.title_cn._text)

    // 字段项目s
    let items = {}
    _.mapValues(group.items, (rule, field) => {
      let opts = renderFieldMap(rule, field, ctx.lang)
      if (opts.flat) { // 扁平item 转 items -> 特殊build
        const cusData = opts.value(data, ctx)
        for (let idx in cusData) {
          let _item = cusData[idx]
          if (_item['zh-CN']) {
            if (ctx.lang === 'zh-CN') {
              _item = _.merge({}, _item, _item['zh-CN'])
            }
            delete _item['zh-CN']
          }
          items[field + '_' + idx] = _item
        }
      } else {
        items[field] = getFieldValue(data, field, opts, ctx)
      }
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
function getFieldValue(data, field, opts, ctx) {
  let entity = {}

  // 获取值
  if (opts.path) {
    if (opts.filter) { // 存在filter时仅取原始node
      entity.value = xpath(`/Listing/${opts.path}`, data)
    } else {
      entity.value = xpath(`/Listing/${opts.path}/text()`, data).toString()
    }
  } else if (opts.value && typeof opts.value === 'function') {
    entity.value = opts.value(data)
  }

  // filter
  if (opts.filter) {
    entity.value = filter(opts.filter, entity.value, ctx.lang)
    if (typeof entity.value === 'object') {
      entity.value = entity.value.toString()
    }
  }

  // format
  if (opts.format) {
    entity = _.merge({}, entity, format(opts.format, entity.value, ctx.lang))
  }

  // 中文字典翻译
  if (ctx.lang === 'zh-CN' && opts.path && entity.value && entity.value !== '') {
    const type = opts.path.split('/').pop()
    // 仅许可以下字典，参照/data/listhub/langs/enums/下的字典
    if (['Appliance', 'ArchitectureStyle', 'ExpenseType', 'HeatingFuel', 'ParkingType'].indexOf(type) !== -1) { // 仅许可这些
      const dicts = ctx.staticData(`listhub/langs/enums/${type}`)
      if (dicts[entity.value] && dicts[entity.value] !== '') {
        entity.value = dicts[entity.value]
      }
    }
  }

  entity = _.merge({}, opts, entity)

  // 清除无用配置项
  _.forEach(['path', 'format', 'filter', 'values'], d => delete entity[d])

  return entity
}

/**
 * 获取values
 */
export function getChineseValues(field) {

}