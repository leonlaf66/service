require('babel-core/register')({
  'presets': [
    'stage-3',
    ["latest-node", { "target": "current" }]
  ],
  "plugins": [
      "transform-runtime"
  ]
})

require('./tasks')