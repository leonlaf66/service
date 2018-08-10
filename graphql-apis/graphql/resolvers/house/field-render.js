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
    
    case 'money':
      return lang === 'zh-CN' ? { value, suffix: '美元' } : {prefix: '$', value}
  }
}

/*
if (intval($data['value']) === 0) {
    $data['is_empty'] = true;
    break;
}
if (is_chinese()) {
    $data = array_merge($data, [
        'value' => number_format(intval(floatval($data['value']) * 0.092903), 0),
        'suffix' => '平方米'
    ]);
} else {
    $data = array_merge($data, [
        'value' => number_format(floatval($data['value']), 0),
        'suffix' => 'Sq.Ft'
    ]);
}
*/