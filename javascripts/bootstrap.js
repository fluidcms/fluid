(function () {
    requirejs.config({
        baseUrl: "javascripts/",
        urlArgs: (new Date()).getTime(), // !! Remove for production
        paths: {
            'async': 'vendor/async-0.2.10.min',
            'backbone': 'vendor/backbone-1.1.2.min',
            'marionette': 'vendor/backbone.marionette-1.6.4.min',
            'ejs': 'vendor/ejs-1.0.0.min',
            'jquery': 'vendor/jquery-2.1.0.min',
            'qtip': 'vendor/jquery-qtip-2.0.1-111-nightly.min',
            'jquery-ui': 'vendor/jquery-ui-1.10.4.min',
            'text': 'vendor/text-2.0.10.min',
            'underscore': 'vendor/underscore-1.6.0.min',
            'when': 'vendor/when-3.0.0.min'
        },
        shim: {
            'underscore': {
                exports: '_'
            },
            'backbone': {
                deps: ['underscore', 'jquery'],
                exports: 'Backbone'
            },
            'marionette': {
                deps : ['jquery', 'underscore', 'backbone'],
                exports : 'Marionette'
            },
            'jquery-ui': {
                deps: ['jquery']
            },
            'ejs': {
                deps: ['backbone'],
                exports: 'EJS'
            },
            'qtip': {
                deps: ['jquery']
            }
        }
    });
})();