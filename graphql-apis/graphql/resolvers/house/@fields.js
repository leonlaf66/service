import knex from 'local/knex'
import mlsHouseRoi from './mls/HouseRoi'
import listhubHouseRoi from './listhub/HouseRoi'
import filter from './@field.filter'
import lodash from 'lodash'

const photo = (d, { idx, w, h }) => {
  if (d.area_id === 'ma') {
      idx -= 1
      return `http://media.mlspin.com/Photo.aspx?mls=${d.list_no}&n=${idx}&w=${w}&h=${h}`;
  }
  if (!d.mls_id) return null;
  return `http://photos.listhub.net/${d.mls_id}/${d.list_no}/${idx}`;
}

export default {
  nm (d, args, { lang }) {
    let items = []
    if (lang === 'zh-CN') {
      items.push(d.info.city_name[1] + filter.propName(d.prop_type)[1])
      if (d.info.is_sd) {
        items.push('学区房')
      }
      if (['MF', 'SF', 'RN', 'CC'].includes(d.prop_type)) {
        items.push(`${d.no_beds}室${d.no_baths[0]}卫`)
      }
    } else {
      items.push(d.info.city_name[0] + ' ' + filter.propName(d.prop_type)[0])
      if (['MF', 'SF', 'RN', 'CC'].includes(d.prop_type)) {
        items.push(`${filter.singular2plural(d.no_beds, 'bed', 'beds')} ${filter.singular2plural(d.no_baths[0], 'bath', 'baths')}`)
      }
    }
    
    return items.join(',')
  },
  photo (d, args) {
    return photo(d, args)
  },
  photos (d, {w, h}) {
    if (!d.__is_detail) return []
    let cnt = d.info.photo_count
    let items = []
    for (let i = 1; i <= cnt; i ++) {
      items.push(photo(d, { idx: i, w, h}))
    }
    return items
  },
  loc (d) {
    return d.info.loc
  },
  photo_cnt (d) {
    return d.info.photo_count
  },
  area (d, args, { staticData }) {
    if (d.info.area && /[A-Z0-9]/.test(d.info.area) && d.info.area.length === 3) {
      let areaNamesMap = staticData('areas/area')
      if (areaNamesMap.hasOwnProperty(d.info.area)) {
        d.info.area = areaNamesMap[d.info.area]
      }
    }
    return d.info.area
  },
  roi (d) {
    if (!d.__is_detail) return []
    
    if (d.area_id === 'ma') {
      return mlsHouseRoi(d)
    }
    return listhubHouseRoi(d)
  },
  async polygons (d, args, { staticData }) {
    if (!d.__is_detail) return []
    if (!d.city_id) return []

    const city = await knex('city_index')
      .select('name')
      .where('id', `${d.area_id.toUpperCase()}${d.city_id}`)
      .first();
    
    if (!city) return []
    return staticData(`polygons/${d.area_id.toUpperCase()}/${lodash.kebabCase(city.name)}`, [])
  }
}