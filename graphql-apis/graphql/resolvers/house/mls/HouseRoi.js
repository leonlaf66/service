import knex from 'local/knex'
import numeral from 'numeral'
import { merge } from 'lodash'

async function getAveRoi (zipCode) {
  let d = await knex('zipcode_roi_ave')
    .select({ 'ave_roi_cash': 'AVE_ROI_CASH' }, { 'ave_annual_income_cash': 'AVE_ANNUAL_INCOME_CASH' })
    .where('ZIP_CODE', zipCode)
    .first()

  if (!d) return false

  d.ave_roi_cash = numeral(d.ave_roi_cash).format('0.0000')

  return d
}

export default async house => {
  let data = {
    'est_roi_cash': house.estimation ? house.estimation.est_roi : null,
    'ave_roi_cash': null,
    'est_annual_income_cash': house.estimation ? house.estimation.est_rental : null,
    'ave_annual_income_cash': null
  };

  if (house.postal_code) {
    let aveData
    if (aveData = await getAveRoi(house.postal_code)) {
        data = merge(data, aveData);
    }
  }

  return data
}
