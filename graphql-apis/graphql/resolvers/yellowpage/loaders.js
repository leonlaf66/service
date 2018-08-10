import DataLoader from 'dataloader'
import knex from 'local/knex'
import { mapTo } from 'local/dataloader-map'

export const yellowPage = new DataLoader(ids => {
  return knex('yellow_page')
    .whereIn('id', ids)
    .then(rows => {
      return mapTo(ids, r => r.id)(rows)
    })
})