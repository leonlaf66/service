const city = require('local/cities');
const sds = require('local/sds');

module.exports = {
  list_no: d => d.list_no,
  city_id: d => cityId(d),
  parent_city_id: (d, res) => parentCityId(d.state, res.city_id),
  location: (d, res) => location(d, res),
  zip_code: d => d.postal_code,
  prop_type_idx: d => ['RN', 'SF', 'MF', 'CC', 'CI', 'BU', 'LD'].indexOf(d.prop_type),
  price_range_idx: d => priceRangeIdx(d.list_price),
  list_price: d => d.list_price,
  square_feet_range_idx: d => squareFeetRangeIdx(d.square_feet),
  square_feet: d => d.square_feet,
  no_beds: d => d.beds,
  no_bathrooms: d => d.full_baths + d.half_baths,
  no_parkings: d => d.parking_spaces,
  has_garage: d => d.has_garage,
  list_date: d => d.list_date,
  latitude: d => d.latitude,
  longitude: d => d.longitude,
  school_district_id: (d, res) => schoolDistrictId(res),
  subway_line_id: d => d.$subway ? d.$subway.lineIds : [],
  subway_postion_ids: d => d.$subway ? d.$subway.stationIds : [],
  prop_type_rn: d => d.prop_type === 'RN' ? 1 : 0,
  prop_type_sf: d => d.prop_type === 'SF' ? 1 : 0,
  prop_type_mf: d => d.prop_type === 'MF' ? 1 : 0,
  prop_type_cc: d => d.prop_type === 'CC' ? 1 : 0,
  prop_type_ci: d => d.prop_type === 'CI' ? 1 : 0,
  prop_type_bu: d => d.prop_type === 'BU' ? 1 : 0,
  prop_type_ld: d => d.prop_type === 'LD' ? 1 : 0
};

function cityId(d) {
  return d.$city ? d.$city.id : null;
}

function location(d, res) {
  if (d.state === 'MA') {
    let findCity = city.find(d.state, res.city_id);
    d.location = d.location.replace('{%city%}', findCity ? findCity.name : 'Unknow city');
  }
  return d.location;
}

function parentCityId(state, city_id) {
  if (state !== 'CA') return null
  if (!city_id) return 0

  return db('city_ca')
    .where('id', city_id)
    .first()
    .then(d => d.id)
}

function priceRangeIdx(listPrice) {
  if (!listPrice) return 0;

  const rules = ['-500000', '500000-1000000', '1000000-1500000', '1500000-2000000', '2000000-'];

  listPrice = Number.parseInt(listPrice);
  for (let idx in rules) {
    let [start, end] = rules[idx].split('-');
    start = start === '' ? 0 : Number.parseInt(start);
    end = end === '' ? 999999999999 : Number.parseInt(end);

    if (listPrice >= start && listPrice < end) {
      return Number.parseInt(idx) + 1;
    }
  }

  throw new Error('非法的价格区间: ' + listPrice);
}

function squareFeetRangeIdx(squareFeet) {
  if (!squareFeet) return 0;

  const rules = ['-1000', '1000-2000', '2000-3000', '3000'];

  squareFeet = Number.parseInt(squareFeet);
  for (let idx in rules) {
    let [start, end] = rules[idx].split('-');
    start = start === '' ? 0 : Number.parseInt(start);
    end = end === '' ? 999999999999 : Number.parseInt(end);

    if (squareFeet >= start && squareFeet < end) {
      return Number.parseInt(idx) + 1;
    }
  };

  throw new Error('非法的面积区间: ' + squareFeet);
}

function schoolDistrictId(d) {
  if (d.state !== 'MA') return null;
  let sd = sds.find(d.city_id)
  return sd ? sd.id : null
}