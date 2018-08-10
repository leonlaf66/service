import config from 'config'
import knex from 'local/knex'
import pagination from 'local/pagination'
import houseBuilder from 'local/house-builder'
import searchApplyTo from './house/utils/searchApplyTo'
import houseFields from './house/@fields'
import houseDetail from './house/detail'
import { house as houseLoader } from './house/loaders'

module.exports = {
  House: {
    nm: houseFields.nm,
    loc: houseFields.loc,
    photo_cnt: houseFields.photo_cnt,
    photo: houseFields.photo,
    roi: houseFields.roi,
    prop: d => d.prop_type,
    price: d => d.list_price,
    date: d => d.list_date,
    beds: d => d.no_beds,
    baths: d => d.no_baths,
    zip_code: d => d.postal_code,
    polygons: houseFields.polygons,
    details: houseDetail,
    associated_houses
  },
  TopHouse: {
    image: ({ image }) => `${config.media.baseUrl}/${image}`,
    house: ({ id }) => houseLoader.load(id)
  },
  Query: {
    search_houses,
    house,
    houses,
    nearby_houses,
    area_house_summeries,
    top_houses,
    autocomplete_cities
  }
}

/**
 * 搜索房源
 */
async function search_houses (_, { q, filters, sort, page, page_size }, ctx, info) {
  // 建立查询
  let query = houseBuilder(ctx.area_id)

  // 售房/租房区分
  if (filters && filters.props && filters.props.length === 0) {
    query.where('prop_type', '<>', 'RN')
  }

  // 应用搜索条件
  await searchApplyTo(ctx, query, q, filters, sort)
  query.orderBy('list_no', 'asc')

  // 建立分页
  return pagination(query, page, page_size)
}

/**
 * 获取房源详情
 */
function house (_, { id }, ctx, info) {
  return houseLoader.load(id).then(d => {
    d.__is_detail = true
    return d
  })
}

/**
 获取相关联房源
 */
function associated_houses (d, { limit }, { area_id }) {
  if (!d.__is_detail) return []

  const prop_types = ['SF', 'CC'].includes(d.prop_type) ? ['SF', 'CC'] : [d.prop_type]
  return houseBuilder(area_id)
    .where('city_id', d.city_id)
    .whereIn('prop_type', prop_types)
    .where('list_price', '>', 30000)
    .whereNot('list_no', d.id)
    .clearOrder()
    .orderBy('list_price', 'ASC')
    .orderBy('list_date', 'DESC')
    .orderBy('list_no', 'ASC')
    .limit(limit)
}

/**
 * 列表房源
 */
function houses (_source, { ids }, ctx, info) {
  return ids.map(id => houseLoader.load(id))
}

/**
 * 周边房源
 */
function nearby_houses (_source, { latlon, limit, distance }, ctx, info) {
  distance *= 1000

  let query = houseBuilder(ctx.area_id)
    .whereRaw(`earth_box(ll_to_earth(latlng[1]::double precision, latlng[2]::double precision),${distance}::double precision) @> ll_to_earth(?, ?)`, [...Object.values(latlon)])

  const fields = getFieldNames(info)
  return query.limit(limit).pluck('list_no').then(ids => {
    return ids.map(id => houseLoader.load(id))
  })
}

/*
 * 获取统计
 */
async function area_house_summeries (_, args, ctx) {
  /*totals*/
  const totals = await (async areaId => {
    let rows = await houseBuilder(areaId)
      .select('prop_type', {'total': knex.raw('count(*)')})
      .groupBy('prop_type')

    let totals = {
      sell: 0,
      renal: 0
    };

    for (let k in rows) {
      let row = rows[k]
      if (row.prop_type === 'RN') {
        totals.renal += Number.parseInt(row.total)
      } else {
        totals.sell += Number.parseInt(row.total)
      }
    }

    return totals
  })(ctx.area_id);

  const marketes = await (async areaId => {
    let rows = await knex('site_chart_setting')
      .where('area_id', areaId)

    let marketes = {};

    for (let k in rows) {
      let row = rows[k]
      let field = row.path.split('/')[1].replace(/\-/g, '_')
      marketes[field] = row.data
    }

    return marketes
  })(ctx.area_id);

  return {
    totals,
    marketes
  }
}

/**
 * 精品房源推荐
**/
async function top_houses (_, { limit }, ctx) {
  const d = await knex('site_setting')
    .where('path', 'home.luxury.houses')
    .where('site_id', ctx.area_id)
    .first()
    .get('value')

  return JSON.parse(d)
}

/**
 * 获取自动完成城市列表
 */
import getAutocompleteCitiesForMls from './house/mls/AutocompleteCities'
import getAutocompleteCitiesForListhub from './house/listhub/AutocompleteCities'

async function autocomplete_cities (_, { q, limit}, ctx) {
  const areaId = ctx.area_id

  if (areaId === 'ma') {
    return getAutocompleteCitiesForMls(q, limit)
  }
  return getAutocompleteCitiesForListhub(areaId, q, limit)
}