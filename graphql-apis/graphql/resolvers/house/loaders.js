import DataLoader from 'dataloader'
import knex from 'local/knex'
import { mapTo } from 'local/dataloader-map'

export const house = new DataLoader(keys => {
  return knex('house_index_v2')
    .select('*')
    .column({'id': 'list_no'})
    .whereIn('list_no', keys)
    .then(rows => mapTo(keys, r => r.id)(rows))
}, { cache: false })