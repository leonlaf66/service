const objGet = require('object-get')
const empty = require('local/empty-helper').empty

module.exports = (obj, expression) => {
  let value = null
  try {
    value = objGet(obj, expression)
  } catch (e) {}

  let filter = null

  return {
    filter (fn) {
      filter = fn
      return this
    },
    val (defValue = null) {
      if (empty(value)) {
        return defValue
      }

      if (filter) {
        value = filter(value)
      }
      return value
    }
  }
}