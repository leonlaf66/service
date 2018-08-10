import DataLoader from 'dataloader'
import knex from 'local/knex'
import { mapTo } from 'local/dataloader-map'

export default new DataLoader(ids => (
  knex('city_index')
    .whereIn('id', ids)
    .then(rows => mapTo(ids, r => r.id)(rows))
))