const location = require('./fields/location');
const detail = require('./fields/detail');

const float = v => {
  if (!v) return null;
  return Number.parseFloat(v);
};
const int = v => {
  if (!v) return null;
  return Number.parseInt(v);
};

module.exports = {
  list_no: d => d.list_no,
  prop_type: d => d.prop_type,
  
  /*Base*/
  beds: d => int(d.json_data.no_bedrooms),
  full_baths: d => int(d.json_data.no_full_baths),
  half_baths: d => int(d.json_data.no_half_baths),
  square_feet: d => float(d.json_data.square_feet),
  lot_size: d => int(d.json_data.lot_size),
  parking_spaces: d => int(d.json_data.parking_spaces),
  has_garage: d => int(d.json_data.garage_spaces) > 0,

  /*Address*/
  location: d => location(d),
  postal_code: d => d.json_data.zip_code,
  city_code: d => d.json_data.town,

  /*Location*/
  latitude: d => d.json_data.latitude,
  longitude: d => d.json_data.longitude,

  list_price: d => float(d.json_data.list_price),
  list_date: d => d.json_data.list_date,
  est_sale: d => d.est_sale,
  status: d => d.status,
  estimation: d => float(d.estimation),
  taxes: d => float(d.json_data.taxes),
  detail: d => detail(d.json_data),

  photo_count: (d) => int(d.json_data.photo_count),
  area: d => d.json_data.area || null ,
  mls_id: d => 'mls',
  state: d => 'MA',
  update_date: d => d.update_date,
};