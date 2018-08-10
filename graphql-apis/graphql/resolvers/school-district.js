import knex from 'local/knex'
import * as loaders from './school-district/loaders'
import houseBuilder from 'local/house-builder'

module.exports = {
  SchoolDistrict: {
    code:         d => d.json.code,
    name:         (d, _, { tt })   => tt(d.json.name),
    name2:        (d, _, { lang }) => lang === 'zh-CN' ? d.json.name[0] : d.json.name[1], 
    rating:       d => d.json.rating,
    description:  (d, _, { tt })   => tt(d.json.description),
    advantage:    (d, _, { tt })   => tt(d.json.advantage),
    special:      d => d.json.special,
    sat:          d => d.json.sat,
    racials:      d => d.json.racials,
    schools:      d => d.json.schools,
    environments: (d, _, { tt }) => (
      d.json.environments.map(d  => (
        {
          name: tt(d.name, d.name_cn),
          description: tt(d.description, d.description_cn)
        }
      ))
    ),
    features:     d => d.json.features,
    comments:     d => d.json.comments,
    environment:  d => d.json.environment,
    k12:          d => d.json.k12,
    summeries:    d => (
      loaders.schoolDistrictSummary.load(d.json.code).then(r => r ? r.data : null)
    ),
    hot_houses
  },
  Query: {
    school_district_list: (_, args, { area_id }) => (
      area_id === 'ma' ? knex('schooldistrict') : []
    ),
    school_district: (_, { id }, { area_id }) => {
      return area_id === 'ma' 
        ? knex('schooldistrict')
          .where('id', id)
          .first()
          .then(d => {
            d.__is_detail = true
            return d
          })
        : {}
    }
  }
}

async function hot_houses({ __is_detail, code }, { limit }) {
  if (__is_detail) {
    const cityIds = await knex('town')
      .whereIn('short_name', code.split('/'))
      .pluck('id')

    return houseBuilder('ma')
      .whereIn('city_id', cityIds)
      .limit(limit)
  }
  return []
}
