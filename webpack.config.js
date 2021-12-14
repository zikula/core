const Encore = require('@symfony/webpack-encore');
const FileManagerPlugin = require('filemanager-webpack-plugin');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

/**
 * Legacy Zikula packages - @deprecated remove with 4x
 */
const copyPaths = [
    // legacy jquery
    {source: './node_modules/jquery/dist', destination: './public/jquery'},
    {source: './node_modules/jquery/dist/jquery.js', destination: './public/jquery/jquery-built.js'},
    // legacy jquery-ui
    {source: './node_modules/components-jqueryui/themes', destination: './public/jqueryui/themes'},
    {source: './node_modules/components-jqueryui/ui', destination: './public/jqueryui/ui'},
    {source: './node_modules/components-jqueryui/jquery-ui.js', destination: './public/jqueryui/jquery-ui-built.js'},
    // legacy bootstrap 4
    {source: './node_modules/bootstrap4/dist', destination: './public/bootstrap'},
    {source: './node_modules/bootstrap4/dist/js/bootstrap.js', destination: './public/bootstrap/bootstrap-built.js'},
    // bootswatch 4
    {source: './node_modules/bootswatch4', destination: './public/bootswatch'},        
    // jstree
    {source: './node_modules/jstree/dist', destination: './public/jstree/dist'},
    {source: './node_modules/jstree/dist/jstree.js', destination: './public/jstree/jstree-built.js'},
    {source: './node_modules/jstree/dist/themes/default/style.css', destination: './public/jstree/jstree-built.css'},
    // FA
    {source: './node_modules/@fortawesome/fontawesome-free/css', destination: './public/font-awesome/css'},
    {source: './node_modules/@fortawesome/fontawesome-free/webfonts', destination: './public/font-awesome/webfonts'},
    {source: './node_modules/@fortawesome/fontawesome-free/css/fontawesome.css', destination: './public/font-awesome/font-awesome-built.css'},
    // MMenu
    {source: './node_modules/mmenu-js/dist/mmenu.js', destination: './public/mmenu/js/mmenu.js'},
    {source: './node_modules/mmenu-js/dist/mmenu.css', destination: './public/mmenu/css/mmenu.css'},
    // iconpicker
    {source: './node_modules/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.css', destination: './public/fontawesome-iconpicker/fontawesome-iconpicker.css'},
    {source: './node_modules/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css', destination: './public/fontawesome-iconpicker/fontawesome-iconpicker.min.css'},
    {source: './node_modules/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js', destination: './public/fontawesome-iconpicker/fontawesome-iconpicker.js'},
    {source: './node_modules/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js', destination: './public/fontawesome-iconpicker/fontawesome-iconpicker.min.js'},
    // magnific-popup
    {source: './node_modules/magnific-popup/dist', destination: './public/magnific-popup'},
];

/**
 * Currently default config - @deprecated remove with 4x
 */
Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableStimulusBridge('./assets/controllers.json')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .addPlugin(new FileManagerPlugin({
        events: {
            onEnd: {
                copy: copyPaths,
            }
        }
    }))
;

// build the first configuration
// https://symfony.com/doc/5.4/frontend/encore/advanced-config.html#defining-multiple-webpack-configurations
const legacyConfig = Encore.getWebpackConfig();
// Set a unique name for the config (needed later!)
legacyConfig.name = 'legacyConfig';

// reset Encore to build the second config
Encore.reset();

/**
 *  Core 4 default config
 */
 Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/default')
    // public path used by the web server to access the output path
    .setPublicPath('/build/default')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/default/app.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/default/controllers.json')

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

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    // .enableSassLoader()

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

const defaultConfig = Encore.getWebpackConfig();
defaultConfig.name = 'defaultConfig';

module.exports = [legacyConfig, defaultConfig];