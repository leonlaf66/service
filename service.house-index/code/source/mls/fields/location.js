const knex = require('local/knex').db;
const ucwords = require('ucwords');

module.exports = async d => {
  let data = d.json_data;

  let result = [];
  let itemsParts = [];

  // 街道
  if (data.street_num) {
    itemsParts.push(data.street_num);
  }
  if (data.street_name) {
    itemsParts.push(ucwords(data.street_name.toLowerCase()));
  }
  result.push(itemsParts.join(' '));
  itemsParts = []; // 归位

  // 单元
  if (['RN', 'CC'].includes(d.prop_type) && data.unit_no) {
    result.push('Unit ' + data.unit_no);
  }

  // 城市
  itemsParts.push('{%city%}');
  itemsParts.push('MA');
  if (data.zip_code) {
    itemsParts.push(data.zip_code);
  }
  result.push(itemsParts.join(' '));

  return result.join(', ');
};
