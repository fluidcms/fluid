(function () {
    var root = this;

    require.config({
        baseUrl: "javascripts/fluid/",
        urlArgs: (new Date()).getTime(), // !! Remove for production
        paths: {
            async: '../vendor/async',
            jquery: '../vendor/jquery',
            "jquery-ui-origin": '../vendor/jquery-ui',
            "jquery-ui": '../vendor/jquery-ui-custom',
            underscore: '../vendor/underscore',
            backbone: '../vendor/backbone',
            ejs: '../vendor/ejs',
            qtip: '../vendor/jquery-qtip'
        },
        shim: {
            underscore: { exports: '_' },
            backbone: { deps: ['underscore', 'jquery'], exports: 'Backbone' },
            ejs: { deps: ['backbone'], exports: 'EJS' },
            "jquery-ui": { deps: ['jquery-ui-origin'], exports: 'jUI' },
            qtip: { deps: ['jquery'] }
        }
    });

    require(['app'], function (fluid) {
        fluid.run();
    });

})();

function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?|&])" + key + "=.*?(&|$)", "i");
    separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        return uri + separator + key + "=" + value;
    }
}

function randomString(length) {
    var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
    var randomstring = '';
    for (var i = 0; i < length; i++) {
        var rnum = Math.floor(Math.random() * chars.length);
        randomstring += chars.substring(rnum, rnum + 1);
    }
    return randomstring;
}
