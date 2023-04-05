const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  pwa: {
    name: "Salon Booking Plugin",
    themeColor: "#ffd100"
  },
  filenameHashing: false,
  publicPath: process.env.NODE_ENV === 'production'
    ? '/{SLN_PWA_DIST_PATH}/'
    : '/'
})
