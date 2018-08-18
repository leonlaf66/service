import _ from 'validator'

const baseValidates = {
  required (val) {
    if (_.isEmpty(val)) {
      return ['is required.', '不能为空.']
    }
  },
  email (val) {
    if (! _.isEmail(val)) {
      return ['format is incorrect.', '错误的格式']
    }
  },
  length (val, min, max) {
    if (! _.isLength(val, { min, max })) {
      return [`must have a length between ${min} and ${max}.`, `长度必须在${min}和${max}之间`]
    }
  }
}

const executeField = (field, value, rule, validates) => {
  let message = true,
      items = rule.split('|')

  for (let idx in items) {
    let [vid, args] = items[idx].split(':')
    args = args ? args.split(',') : []

    if (validates[vid]) {
      message = (validates[vid])(value, ...args)
      if (typeof message !== 'undefined') break
    }
  }

  if (typeof message === 'undefined') return true
  return [message, items.indexOf('stop') !== -1]
}

module.exports = (rules, validates = {}) => {
  validates = Object.assign({}, baseValidates, validates)

  return (data, { tt }) => {
    let errors = {}

    for (let field in rules) {
      let result = executeField(field, data[field], rules[field], validates)

      if (true !== result) {
        let [message, isStop] = result
        errors[field] = tt(message)
        if (isStop) break
      }
    }

    return Object.keys(errors).length === 0 ? true : errors
  }
}
