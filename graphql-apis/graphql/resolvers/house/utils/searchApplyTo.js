import _ from 'lodash'
import moment from 'moment'
import knex from 'local/knex'

const filterMaps = {
  props (query, codes) {
    if (codes.length > 0) {
      query.whereIn('prop_type', codes)
    }
  },
  price (query, range) {
    query.whereBetween('list_price', _.values(range))
  },
  city_id (query, id) {
    query.where('city_id', id)
  },
  square_feet (query, range) {
    query.whereBetween('square_feet', _.values(range))
  },
  beds (query, no) {
    query.where('no_beds', '>=', no)
  },
  baths (query, no) {
    query.where('no_baths[1]', '>=', no)
  },
  list_days (query, no) {
    const jgMaps = {'#1': -2, '#2': -7, '#3': -30}

    no = '#' + no
    if (! _.includes(_.keys(jgMaps), no)) return

    const startTime = moment().day(jgMaps[no]).format('YYYY-MM-DD')
    query.where('list_date', '>=', startTime);
  },
  parking_spaces (query, no) {
    query.where('parking_spaces', '>=', no)
  },
  garage_spaces (query, have) {
    query.where('garage_spaces', '>', 0)
  }
}

const orderMaps = {
  '#1': ['list_date', 'desc'],
  '#2': ['list_price', 'asc'],
  '#3': ['list_price', 'asc'],
  '#4': ['no_beds', 'desc'],
  '#5': ['no_beds', 'asc']
}

export default async function (ctx, query, q, filters, order) {
  // q
  if (q && q.replace(/s+/g, '').length > 0) {
    if (/[A-Z0-9]{6,20}/.test(q)) { // 是list_no
      query.where('list_no', q)
    } else if (!isNaN(q) && q.length === 5) { // 是邮编
      query.where('postal_code', q)
    } else { // 是城市名称
      const cityTable = ctx.area_id === 'ma' ? 'town' : 'city'
      const city = await knex(cityTable).select('id')
        .where('state', ctx.area_id.toUpperCase())
        .whereRaw('lower(name) = ? or name_cn = ?', [q.toLowerCase(), q])
        .first()
      if (city) {
        query.where('city_id', city.id)
      } else {
        query.whereRaw('1=2')
      }
    }
  }

  // filters
  if (!filters) filters = {}
  _.forEach(filters, (val, field) => {
    if (filterMaps.hasOwnProperty(field)) {
      filterMaps[field](query, val)
    }
  })

  // order
  if (!order) order = 1
  const orderId = '#' + order;
  if (orderMaps.hasOwnProperty(orderId)) {
    query.orderBy(...orderMaps[orderId])
  }
}