const { db } = require('local/knex')

let results = []

const sd = {
  async load() {
    results = await db('schooldistrict')
      .select('id', 'json', 'city_id')
      .then(rows => buildTo(rows))
  },
  find(cityId) {
    return results.hasOwnProperty(cityId) ? results[cityId] : null
  }
}

function buildTo(rows) {
  let result = {}

  rows.forEach(row => {
    row.city_id.forEach(id => {
      result[id] = row
    })
  })

  return result
}

module.exports = sd
