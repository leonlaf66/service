import knex from 'local/knex'
import pagination from 'local/pagination'
import cityLoaders from './city/loaders'

module.exports = {
  HouseNewsletter: {
    data: newsletter_data,
  },
  Query: {
    find_house_newsletter
  },
  Mutation: {
    create_house_newsletter,
    remove_house_newsletter
  }
}

async function newsletter_data (d, args, { tt }) {
  let data = JSON.parse(d.data)
  
  if (data.city) {
    let cityId = d.area_id.toUpperCase() + data.city
    data.city = await cityLoaders.load(cityId).then(d => {
      d.name = tt(d.name, d.name_cn)
      delete d.name_cn
      return d
    })
  }

  if (data.price_range) {
    console.log(data.price_range)
    data.price_range = data.price_range.split('-')
  }
  
  return data
}

async function find_house_newsletter (_, { first, skip }, { user: { id } }) {
  let query = knex('house_member_newsletter')
    .where('user_id', id)

  return pagination(query, first, skip)
}

async function create_house_newsletter () {
  return false
}

async function remove_house_newsletter () {
  return false
}