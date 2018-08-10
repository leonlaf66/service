import fs from 'fs'

export default (name) => {
  let file = process.cwd() + '/data/' + name + '.json'
  let data = fs.readFileSync(file, 'utf-8')
  return JSON.parse(data)
}