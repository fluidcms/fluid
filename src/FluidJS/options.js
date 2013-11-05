module.exports = {
    findNestedDependencies: true,
    appDir: 'src/FluidJS',
    baseUrl: '.',
    mainConfigFile: 'src/FluidJS/bootstrap.js',
    dir: 'public/javascripts',
    modules: [
        {
            name: 'bootstrap',
            exclude: [
                'async',
                'autobahn',
                'backbone',
                'ejs',
                'jquery',
                'qtip',
                'jquery-ui-origin',
                'jquery-ui',
                'require',
                'sanitize',
                'text',
                'underscore',
                'when'
            ]
        }
    ]
};
