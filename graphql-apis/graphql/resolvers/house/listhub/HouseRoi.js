import knex from 'local/knex'
import numeral from 'numeral'
import { merge } from 'lodash'

export default async house => {
  return {
    'est_roi_cash': null,
    'ave_roi_cash': null,
    'est_annual_income_cash': null,
    'ave_annual_income_cash': null
  }
}
