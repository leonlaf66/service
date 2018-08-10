require('babel-core/register')({
  'presets': [
    'stage-3',
    ["latest-node", { "target": "current" }]
  ],
  "plugins": [
      "transform-runtime"
  ]
})

// require('babel-polyfill') // 不要这个了

require('./server')