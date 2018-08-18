import knex from 'local/knex'
import pagination from 'local/pagination'
import { house as houseLoader } from './house/loaders'

module.exports = {
  HouseFavorite: {
    house: d => houseLoader.load(d.list_no)
  },
  Query: {
    find_favorite_houses: (_, { rental, first, skip }, { user: { id } }) => {
      let query = knex('house_member_favority')
        .where('user_id', id)
        .where('property_type', (rental ? '=' : '<>'), 'RN')

      return pagination(query, first, skip)
    }
  },
  Mutation: {
    remove_favorite_house: () => {
      return false
    }
  }
}