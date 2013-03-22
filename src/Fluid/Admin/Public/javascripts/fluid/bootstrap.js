(function () {
	var root = this;
 
	require.config({
		baseUrl: "javascripts/fluid/",
		urlArgs: (new Date()).getTime(), // !! Remove for production
		paths: {
			jquery: '../vendor/jquery',
			'jquery-ui': '../vendor/jquery-ui',
			underscore: '../vendor/underscore',
			backbone: '../vendor/backbone',
			ejs: '../vendor/ejs'
		},
		shim: {
			underscore: { exports: '_' },
			backbone: { deps: ['underscore', 'jquery'], exports: 'Backbone' },
			ejs: { deps: ['backbone'], exports: 'EJS' }
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