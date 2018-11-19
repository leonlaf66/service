import knex from 'local/knex'

export default (areaId) => {
  return knex('house_index_v2')
    .select('*')
    .column({id: 'list_no'})
    .where('area_id', areaId)
    .where('city_id', '>', 0)
    .where('is_online_abled', true)
    .orderBy('list_date', 'desc')
}