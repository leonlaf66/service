import DataLoader from 'dataloader'
import knex from 'local/knex'
import { mapTo } from 'local/dataloader-map'

export const member = new DataLoader(ids => {
  return knex('member')
    .whereIn('id', ids)
    .then(rows => mapTo(ids, r => r.id)(rows))
}, { cache: false })

export const memberProfile = new DataLoader(ids => {
  return knex('member_profile')
    .whereIn('user_id', ids)
    .then(rows => mapTo(ids, r => r.user_id)(rows))
}, { cache: false })