const { Client } = require('pg');
const dbConfigs = require('./config').db;
const pg = new Client(dbConfigs);
const schedule = require('node-schedule');
const fs = require('fs');

var eventCallback = null;
schedule.scheduleJob('0 * * * *', () => {
  /*
  var lastData = fs.readFileSync('./mls.key', 'utf8');
  pg.query('select * from mls_rets where update_data > ?', [$lastData]).then(rows => {
    for (let i in rows) {
      eventCallback(rows[i]);
    }
  })*/
  var myDate = new Date();
  eventCallback(myDate.toLocaleString());
});

module.exports  = fn => {
  eventCallback = fn
};
