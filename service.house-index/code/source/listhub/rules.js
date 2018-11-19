const aGet = require('local/object-get');
const location = require('./fields/location');
const detail = require('./fields/detail');
const propTypeId = require('./fields/prop-type');
const status = require('./fields/status');

const int = Number.parseInt;
const float = Number.parseFloat;

module.exports = {
  list_no: d => d.list_no,
  prop_type: d => propTypeId(aGet(d.json_data, 'PropertyType[0]').val(), aGet(d.json_data, 'PropertySubType[0]').val()),
  
  /*Base*/
  beds: d => aGet(d.json_data, 'Bedrooms[0]').filter(int).val(),
  full_baths: d => aGet(d.json_data, 'FullBathrooms[0]').filter(int).val(),
  half_baths: d => aGet(d.json_data, 'HalfBathrooms[0]').filter(int).val(),
  square_feet: d => aGet(d.json_data, 'LivingArea[0]').filter(float).val(),
  lot_size: d => aGet(d.json_data, 'LotSize[0]').filter(float).val(),
  parking_spaces: d => aGet(d.json_data, 'NumParkingSpaces[0]').filter(int).val(),
  has_garage: d => null,

  /*Address*/
  location: (d, res) => location(aGet(d.json_data, 'Address[0]').val(), res),
  postal_code: d => aGet(d.json_data, 'Address[0].PostalCode[0]').val(),
  city_code: d => aGet(d.json_data, 'Address[0].City[0]').filter(s => s.toLowerCase()).val(),

  /*Location*/
  latitude: d => aGet(d.json_data, 'Location[0].Latitude[0]').val(),
  longitude: d => aGet(d.json_data, 'Location[0].Longitude[0]').val(),

  list_price: d => aGet(d.json_data, 'ListPrice[0]').filter(float).val(),
  list_date: d => aGet(d.json_data, 'ListingDate[0]').val(),
  est_sale: d => null, //listhub没有这个属性
  status: d => aGet(d.json_data, 'ListingStatus[0]').filter(status).val(),
  estimation: d => d.estimation,
  taxes: d => aGet(d.json_data, 'Taxes[0].Tax[0].Amount[0]').filter(float).val(),
  detail: (d, res) => detail(d.json_data, res),

  photo_count: (d) => aGet(d.json_data, 'Photos[0].Photo').length,
  area: d => null, // listhub未提供该属性
  mls_id: d => aGet(d.json_data, 'MlsId[0]').val(),
  state: d => aGet(d.json_data, 'Address[0].StateOrProvince[0]').val(),
  update_date: d => d.last_update_date,
};