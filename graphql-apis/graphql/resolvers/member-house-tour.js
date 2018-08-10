import knex from 'local/knex'
import pagination from 'local/pagination'
import { house } from './house/loaders'

module.exports = {
  HouseTour: {
    is_confirmed: d => d.status,
    house: d => house.load(d.list_no)
  },
  Query: {
    find_house_tour: (_, { page, page_size }, { user: { id } }) => {
      let query = knex('house_member_tour')
        .where('user_id', id)

      return pagination(query, page, page_size)
    }
  },
  Mutation: {
    remove_house_tour: (_, { id }) => {
      return false
    }
  }
}