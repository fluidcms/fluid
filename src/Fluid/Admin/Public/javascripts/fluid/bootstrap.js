var Fluid = {
	Collection: {},
	Model: {},
	View: {},
	Structure: {}
};

$(document).ready(function() {
	var loaded = 9;
	
	$.getScript('javascripts/fluid/models/site.js', function() { loaded--; });
	$.getScript('javascripts/fluid/models/page.js', function() { loaded--; });
	$.getScript('javascripts/fluid/models/structure.js', function() { loaded--; });
	$.getScript('javascripts/fluid/views/nav.js', function() { loaded--; });
	$.getScript('javascripts/fluid/views/toolbar.js', function() { loaded--; });
	$.getScript('javascripts/fluid/views/contextmenu.js', function() { loaded--; });
	$.getScript('javascripts/fluid/views/modal.js', function() { loaded--; });
	$.getScript('javascripts/fluid/views/structure.js', function() { loaded--; });
	$.getScript('javascripts/fluid/views/pageeditor.js', function() { loaded--; });
	
	var loading = setInterval(function() {
		if (loaded == 0) {
			$.getScript('javascripts/fluid/app.js');
			clearInterval(loading);
		}
	}, 10);	
});
