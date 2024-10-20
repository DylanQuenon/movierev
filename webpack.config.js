const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('headerStyle', './assets/styles/partials/header.scss')
    .addEntry('sliderRevStyle', './assets/styles/partials/_sliderReviews.scss')
    .addEntry('cardNewsStyle', './assets/styles/partials/cardNews.scss')
    .addEntry('cardMediaStyle', './assets/styles/partials/cardMedia.scss')
    .addEntry('sliderNewsStyle', './assets/styles/partials/sliderNews.scss')
    .addEntry('cardReviewsStyle', './assets/styles/partials/cardReviews.scss')
    .addEntry('buttonStyle', './assets/styles/partials/_buttonStyle.scss')
    .addEntry('homeStyle', './assets/styles/pages/home.scss')
    .addEntry('movieStyle', './assets/styles/pages/movies.scss')
    .addEntry('reviewsStyle', './assets/styles/pages/reviews.scss')
    .addEntry('reviewIndiv', './assets/styles/pages/reviewIndiv.scss')
    .addEntry('contactStyle', './assets/styles/pages/contact.scss')
    .addEntry('accountStyle', './assets/styles/pages/account.scss')
    .addEntry('aboutStyle', './assets/styles/pages/about.scss')
    .addEntry('addMovieStyle', './assets/styles/pages/addMovies.scss')
    .addEntry('newIndivStyle', './assets/styles/pages/newindiv.scss')
    .addEntry('mediaIndiv', './assets/styles/pages/mediaindiv.scss')
    .addEntry('newsStyle', './assets/styles/pages/newsStyle.scss')
    .addEntry('baseForm', './assets/styles/templates/baseForm.scss')

    .addEntry('burgerJS', './assets/js/burger.js')
    .addEntry('sliderHomeJS', './assets/js/sliderHome.js')
    .addEntry('casting', './assets/js/casting.js')
    .addEntry('answer', './assets/js/answer.js')
    .addEntry('sliderNews', './assets/js/sliderNews.js')
    .addEntry('sliderReview', './assets/js/sliderReview.js')
    .addEntry('sliderLog', './assets/js/sliderLog.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    .enableStimulusBridge('./assets/controllers.json')

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // enables Sass/SCSS support
    .enableSassLoader()
    .enablePostCssLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
