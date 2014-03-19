(function () {
    requirejs.config({
        baseUrl: "javascripts/",
        urlArgs: (new Date()).getTime(), // !! Remove for production
        paths: {
            'async': 'vendor/async-0.2.5.min',
            'backbone': 'vendor/backbone-1.1.0.min',
            'ejs': 'vendor/ejs-1.0.0.min',
            'jquery': 'vendor/jquery-2.0.3.min',
            'qtip': 'vendor/jquery-qtip-2.0.1-111-nightly.min',
            'jquery-ui': 'vendor/jquery-ui-1.10.3.min',
            'text': 'vendor/text-2.0.10.min',
            'underscore': 'vendor/underscore-1.5.2.min',
            'when': 'vendor/when-2.6.0.min'
        },
        shim: {
            'underscore': { exports: '_' },
            'backbone': { deps: ['underscore', 'jquery'], exports: 'Backbone' },
            'jquery-ui': { deps: ['jquery'] },
            'ejs': { deps: ['backbone'], exports: 'EJS' },
            'qtip': { deps: ['jquery'] }
        }
    });
})();