const aGet = require('local/object-get');

module.exports = (d, res) => {
  let result = [];
  let itemParts = [];

  let fieldValues = {
    street: aGet(d, 'FullStreetAddress[0]').val(),
    state: aGet(d, 'StateOrProvince[0]').val(),
    city: aGet(d, 'City[0]').val(),
    zipCode: aGet(d, 'PostalCode[0]').val(),
    unit: aGet(d, 'UnitNumber[0]').val()
  };

  // 街道
  if (fieldValues.street) {
    result.push(fieldValues.street);
  }

  // 单元
  if (['SF', 'CC'].includes(res.prop_type) && fieldValues.unit) {
    result.push('Unit ' + fieldValues.unit);
  }

  // 城市 + 州 + 邮编
  if (fieldValues.city) {
    itemParts.push(fieldValues.city);
  }
  if (fieldValues.state) {
    itemParts.push(fieldValues.state);
  }
  if (fieldValues.zipCode) {
    itemParts.push(fieldValues.zipCode);
  }
  result.push(itemParts.join(' '));

  return result.join(', ');
};