import DataLoader from 'dataloader'
import knex from 'local/knex'
import houseBuilder from 'local/house-builder'
import { mapTo, mapToMany } from 'local/dataloader-map'
import lodash from 'lodash'

export function summaries(areaId) {
  return knex('site_chart_setting')
    .where('area_id', areaId)
    .then(items => {
      const data = {}
      items.forEach(item => {
        data[lodash.snakeCase(item.path.replace('marketing/', ''))] = item.data
      })
      return data
    })
}

export function settings(areaId) {
  return knex('site_setting')
    .where('site_id', areaId)
    .where('path', 'purchase.mortgage-calculator.interest-rate.default')
    .first()
    .then(r => (
      {
        'sell_rate': Number.parseFloat(JSON.parse(r.value))
      }
    ))
}

export const topHouse = new DataLoader(areaIds => {
  return knex('site_setting')
    .select('id', 'value', 'site_id')
    .where('path', 'home.luxury.houses')
    .whereIn('site_id', areaIds)
    .then(rows => mapTo(areaIds, r => r.site_id)(rows))
    .then(rows => {
      return rows.map(d => d ? JSON.parse(d.value) : [])
    })
}, {
  cacheKeyFn: () => (new Date()).toDateString()
})

export function lastestHouse(limit) {
  return new DataLoader(areaIds => {
    const findSql = areaIds.map(areaId => (
      houseBuilder(areaId)
        .limit(limit)
        .toString()
    ))
    
    return knex.raw(findSql.join('UNION ALL'))
      .then(res => res.rows)
      .then(rows => mapToMany(areaIds, r => r.area_id)(rows))
  }, {
    cacheKeyFn: () => (new Date()).toDateString()
  })
}

export function lastestNews(limit) {
  return new DataLoader(areaIds => {
    const findSql = areaIds.map(areaId => (
      knex('news')
        .select('*')
        .column(knex.raw(`'${areaId}' as it_area_id`))
        .where('area_id', '@>', `{${areaId}}`)
        .orderBy('created_at', 'DESC')
        .limit(limit)
        .toString()
    ))

    return knex.raw(findSql.join('UNION ALL'))
      .then(res => res.rows)
      .then(rows => mapToMany(areaIds, r => r.it_area_id)(rows))
  }, {
    cacheKeyFn: () => (new Date()).toDateString()
  })
}