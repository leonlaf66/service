const { db } = require('local/knex')
const numeral = require('numeral')
const { merge } = require('lodash')

async function getAveRoi (zipCode) {
  let d = await db('zipcode_roi_ave')
    .select({ 'ave_roi_cash': 'AVE_ROI_CASH' }, { 'ave_annual_income_cash': 'AVE_ANNUAL_INCOME_CASH' })
    .where('ZIP_CODE', zipCode)
    .first()

  if (!d) return false

  d.ave_roi_cash = numeral(d.ave_roi_cash).format('0.0000')

  return d
}

module.exports = async (postalCode, estimation) => {
  let data = {
    'est_roi_cash': estimation ? estimation.est_roi : null,
    'ave_roi_cash': null,
    'est_annual_income_cash': estimation ? estimation.est_rental : null,
    'ave_annual_income_cash': null
  };

  if (postalCode) {
    let aveData
    if (aveData = await getAveRoi(postalCode)) {
        data = merge(data, aveData);
    }
  }

  return data
}
