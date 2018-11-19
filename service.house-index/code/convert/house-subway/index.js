const { db } = require('local/knex')
const turf = require('@turf/turf')

let stations = []

const subway = {
  async load() {
    if (stations.length > 0) return; // 禁止重复获取

    stations = await db('subway_station as a')
      .select('a.id', 'b.id as line_id', 'a.latitude', 'a.longitude')
      .innerJoin('subway_line as b', 'b.code', '=', 'a.line_code')
      .orderBy('a.sort_order', 'asc');
  },
  find(lat, lng, radius = 2) {
    let from = turf.point([lat, lng])
    let result = {lineIds: [], stationIds: []}

    stations.forEach(d => {
      let to = turf.point([d.latitude, d.longitude])

      if(turf.distance(from, to, {units: 'miles'}) <= radius / 1.609344) {
        if(!result.lineIds.includes(d.line_id)) {
          result.lineIds.push(d.line_id)
        }
        result.stationIds.push(d.id)
      } else {
        if (result.stationIds.length > 0) { // 如果先匹配，此时没被匹配，基本说明再也不会被匹配了，该结束了
          return result
        }
      }
    })

    return result
  }
}

module.exports = subway
