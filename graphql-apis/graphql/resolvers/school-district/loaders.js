import DataLoader from 'dataloader'
import knex from 'local/knex'
import { mapTo } from 'local/dataloader-map'
import _ from 'lodash'

const dataFormat = data => {
  ['average_price', 'avergage_rental_price'].forEach(field => {
    if (data[field]) {
      data[field] = data[field].replace(/([0-9]+\.[0-9]{2})[0-9]*/, "$1")
    }
  })
}

const getSummaryByCodes = codes => {
  return knex('schooldistrict_setting')
    .select('code', 'path', 'data')
    .whereIn('code', codes)
    .then(items => {
      // build to format for "(code, data)"
      items = _.groupBy(items, 'code')
      items = _.map(items, (rows, code) => {
        const data = {}
        rows.forEach(d => {
          data[_.snakeCase(d.path)] = d.data
        })
        
        dataFormat(data)

        return { code, data }
      })

      return items
    })
    .then(rows => mapTo(codes, r => r.code)(rows))
}

export const schoolDistrictSummary = new DataLoader(getSummaryByCodes)