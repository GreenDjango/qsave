module.exports = {
  lintOnSave: false,
  productionSourceMap: false,
  pwa: {
    name: 'Qsave',
    themeColor: '#4DBA87',
    msTileColor: '#2d89ef',
    appleMobileWebAppCapable: 'no',
    appleMobileWebAppStatusBarStyle: 'black',
    iconPaths: {
      faviconSVG: 'img/favicon.svg',
      favicon32: 'img/favicon-32x32.png',
      favicon16: 'img/favicon-16x16.png',
      appleTouchIcon: 'img/apple-touch-icon.png',
      maskIcon: 'img/safari-pinned-tab.svg',
      msTileImage: 'img/mstile-150x150.png'
    },
    manifestOptions: {
      icons: [
        {
          src: "./img/android-chrome-192x192.png",
          sizes: "192x192",
          type: "image/png"
        },
        {
          src: "./img/android-chrome-512x512.png",
          sizes: "512x512",
          type: "image/png"
        }
      ]
    }
  },
}
