import config from 'config'

export default {
  propName (prop) {
    return config.get('staticData')[prop]
  },
  // 单复数
  singular2plural (no, singularWords, pluralWords) {
    return Number.parseFloat(no) === 1 ? no + ' ' + singularWords : no + ' ' + pluralWords
  }
}