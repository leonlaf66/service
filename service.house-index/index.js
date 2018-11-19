/*
const Queue = require('bull');
const fetchSource = require('./house/source');
const houseProcess = require('./house');

const batchSize = 100;
const queue = new Queue('test', {
  redis: {
    keyPrefix: 'house-index',
  },
  limiter: {
    max: 1000,
    duration: 5000
  }
});

async function load() {
  fetchSource(lastTime, 1000);
}

queue.on('drained', async () => {
  queue.clean(0, 'completed');

  let jobs = await queue.count();
  if (jobs < batchSize) { // 小于5个再加入5个
    load();
  }
});

queue.process(async (job, done) => {
  houseProcess(job.data, done);
});

load(); // 开始

console.log('service ing%c test', 'color:red;');
*/