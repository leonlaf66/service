import DataLoader from 'dataloader'
import knex from 'local/knex'
import { mapTo } from 'local/dataloader-map'

export const news = new DataLoader(ids => {
  return knex('news').whereIn('id', ids)
})

export const newsType = new DataLoader(ids => {
  let q = knex('taxonomy_term')
    .select('id', 'name', 'name_zh')
    .where('taxonomy_id', 3)
    .whereIn('id', ids)
    .orderBy('sort_order', 'asc')
    .then(rows => mapTo(ids, r => r.id)(rows))

  return q.then(items => {
      return items.map(d => {
        if (!d) return null
        return {
          id: d.id,
          name: [d.name, d.name_zh]
        }
      })
    })
}, { cache: false })