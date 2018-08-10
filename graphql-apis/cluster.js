import cluster from 'cluster'
import os from 'os'
import config from 'config'

/*
include cluster from './cluster'
cluster(() => {
  server listen...
})
*/
module.exports = (server) => {
  if (config.multiCpuEnabled && cluster.isMaster) {
    const numCPUs = os.cpus().length
    for (var i = 0; i < numCPUs; i++) {
        cluster.fork()
    }
  } else {
    server()
  }
}