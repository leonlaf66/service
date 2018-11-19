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
  city_ids (query, ids) {
    query.whereIn('city_id', ids)
  },
  square (query, range) {
    query.whereBetween('square_feet', _.values(range))
  },
  beds (query, no) {
    query.where('no_beds', '>=', no)
  },
  baths (query, no) {
    query.where('no_baths[1]', '>=', no)
  },
  ldays (query, no) {
    const jgMaps = {'#1': -2, '#2': -7, '#3': -30}

    no = '#' + no
    if (! _.includes(_.keys(jgMaps), no)) return

    const startTime = moment().day(jgMaps[no]).format('YYYY-MM-DD')
    query.where('list_date', '>=', startTime);
  },
  parking (query, no) {
    query.where('parking_spaces', '>=', no)
  },
  garage (query, have) {
    if (have) {
      query.whereRaw('garage_spaces > 0')
    } else {
      query.whereRaw('garage_spaces = 0')
    }
  },
  latlng (query, { lat, lng }) {
    query.whereRaw('earth_box(ll_to_earth(latlng[1]::numeric, latlng[2]::numeric),2000) @> ll_to_earth(?, ?)', [lat, lng])
  },
  subway_line (query, lineId) {
    query.where('subway_lines', '@>', `{${lineId}}`)
  },
  subway_stations (query, ids) {
    query.where('subway_stations', '&&', `{${ids.join(',')}}`)
  }
}

const orderMaps = {
  '#1': ['list_date', 'desc'],
  '#2': ['list_price', 'asc'],
  '#3': ['list_price', 'desc'],
  '#4': ['no_beds', 'desc'],
  '#5': ['no_beds', 'asc']
}

export default async function (ctx, query, q, filters, order = 1) {
  // q
  if (q && q.replace(/s+/g, '').length > 0) {
    if (/[A-Z0-9]{6,20}/.test(q)) { // 是list_no
      query.where('list_no', q)
    } else if (!isNaN(q) && q.length === 5) { // 是邮编
      query.where('postal_code', q)
    } else { // 是城市名称
      const cityTable = ctx.areaId === 'ma' ? 'town' : 'city'
      const city = await knex(cityTable).select('id')
        .where('state', ctx.areaId.toUpperCase())
        .whereRaw('lower(name) = ? or name_cn = ?', [q.toLowerCase(), q])
        .first()
      if (city) {
        query.where('city_id', city.id)
      } else {
        q = q.replace(/\'/g, '').replace(/[\s]+/g, '&')
        const skey = `to_tsquery('english', '${q}')`
        query.whereRaw(`"skey" @@ ${skey}`)
        query.orderByRaw(`${skey} ASC`)
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