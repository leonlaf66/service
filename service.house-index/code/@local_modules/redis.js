const redis = require('redis');
const config = require('config');

const cache = {};
module.exports.redis = redis;
module.exports.client = (name = 'default') => {
  if (!cache.hasOwnProperty(name)) {
    cache[name] = redis.createClient(config.redis.connection);
  }
  return cache[name];
};