const roiLoader = require('local/roi');

module.exports = {
  location: d => location(d),
  beds: d => d.beds,
  full_baths: d => d.full_baths,
  half_baths: d => d.half_baths,
  square_feet: d => d.square_feet,
  list_price: d => d.list_price,
  list_date: d => d.list_date,
  prop_type: d => d.prop_type,
  city: d => d.$city,
  status: d => d.status,
  parking_spaces: d => d.parking_spaces,
  has_garage: d => d.has_garage,
  area: d => d.area,
  photo_count: d => d.photo_count,
  latitude: d => d.latitude,
  longitude: d => d.longitude,
  taxes: d => d.taxes,
  ant_sold_date: d => null,
  roi: d => roi(d),
  detail: d => ({})
};

function location(d) {
  if (d.state === 'MA') {
    d.location = d.location.replace('{%city%}', d.$city ? d.$city.name : 'Unknow city');
  }
  return d.location;
}

async function roi(d) {
  return await roiLoader(d.postal_code, d.estimation);
}