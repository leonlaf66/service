import knex from 'local/knex'

export default async () => {
  const towns = await knex('town')
    .where('state', 'MA')

  let items = []

  // 按城市名
  for (let k in towns) {
    let town = towns[k]
    items.push({
      'title': town.name,
      'desc': town.name_cn + ', MA'
    })

    if (town.name_cn) {
      items.push({
        'title': town.name_cn,
        'desc': town.name + ', MA'
      })
    }
  }

  // 按邮编
  const zipcodes = await knex('zipcode_town')
    .where('state', 'MA')

  for (let k in zipcodes) {
    let zipcode = zipcodes[k]
    items.push({
      'title': zipcode.zip,
      'desc': zipcode.city_name + zipcode.city_name_cn + ', MA'
    })
  }

  return items
}