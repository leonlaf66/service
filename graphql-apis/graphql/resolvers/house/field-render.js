import _ from 'lodash'
import { select as xpath } from 'xpath'

export function filter (type, value) {
  switch(type) {
    case 'count':
      return value.length;
    case 'values':
      return _.map(value, d => {
        if (d.constructor.name === 'Element') {
          return xpath('text()', d).toString()
        }
        return d
      }).join(',')
  }
}

export function format (type, value, lang) {
  switch(type) {
    case 'area':
    case 'sq.ft':
      value = Number.parseInt(value)
      if (value === 0) {
        return {
          is_empty: true
        }
      }
      if (lang === 'zh-CN') {
        return {
          'value': (value * 0.092903).toFixed(0),
          'suffix': '平方米'
        }
      }
      return {
        'value': value
      }
    case 'yes/no':
      if (lang === 'zh-CN') {
        return { value: value === 'true' ? '是' : '否' }
      }// 否则，走下条规则
    case 'have/not':
      if (lang === 'zh-CN') {
        return { value: value === 'true' ? '有' : '无' }
      }
      return { value: value === 'true' ? 'Yes' : 'No' }
    case 'price':
    case 'money':
      if (lang === 'zh-CN') {
        if (Number.parseInt(value) > 10000) {
          value = Number.parseFloat(value * 1.0 / 10000).toFixed(2);
          return { value, suffix: '万美元' }
        }
        return { value, suffix: '美元' }
      }
      return {prefix: '$', value}
  }
}
