const fs = require('fs')

module.exports = (name, defValue = null) => {
  let file = __dirname.concat('/', name, '.json')
  if (!fs.existsSync(file)) {
    return defValue
  }
  let data = fs.readFileSync(file, 'utf-8')
  return JSON.parse(data)
}