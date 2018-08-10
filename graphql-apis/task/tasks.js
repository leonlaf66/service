import { CronJob } from 'cron'

new CronJob('*/2 * * * * *', function() {
  const now = new Date()
  console.log('1: ', now.toString())
}, null, true, 'America/Los_Angeles')

new CronJob('*/5 * * * * *', function() {
  const now = new Date()
  console.log('2: ', now.toString())
}, null, true, 'America/Los_Angeles')