import knex from 'local/knex'
import DataLoader from 'dataloader'
import _ from 'lodash'

const staticProcess = (lineCodes, rows) => {
  let grows = {}
  for (let idx in rows) {
    let row = rows[idx]
    if (!grows[row.line_code]) grows[row.line_code] = []
    grows[row.line_code].push(row)
  }

  let sortedRows = []
  for (let idx in lineCodes) {
    sortedRows.push(grows[lineCodes[idx]])
  }

  return sortedRows
}

const stationLoaders = new DataLoader(lineCodes => {
  return knex('subway_station')
    .whereIn('line_code', lineCodes)
    .then(rows => {
      return staticProcess(lineCodes, rows)
    })
})

module.exports = {
  SubwayLine: {
    name: (d, args, { tt }) => tt(d.name, d.name_zh),
    bg_color: (d) => d.bgcolor,
    stations: (d) => stationLoaders.load(d.code)
  },
  SubwayStation: {
    name: (d, args, { tt }) => tt(d.name, d.name_zh),
    latlng: (d) => ( { lat: d.latitude, lng: d.longitude } )
  },
  Query: {
    subway: all_subway
  }
}

function all_subway (root, args, { area_id }) {
  if (area_id !== 'ma') return []

  return knex('subway_line').orderBy('sort_order', 'ASC')
}