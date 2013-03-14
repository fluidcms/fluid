$(document).ready(function() {
	var loaded = 6;
	
	$.getScript('javascripts/fluid/page.js', function() { loaded--; });
	$.getScript('javascripts/fluid/toolbar.js', function() { loaded--; });
	$.getScript('javascripts/fluid/contextmenu.js', function() { loaded--; });
	$.getScript('javascripts/fluid/modal.js', function() { loaded--; });
	$.getScript('javascripts/fluid/structure.js', function() { loaded--; });
	$.getScript('javascripts/fluid/pageeditor.js', function() { loaded--; });
	
	var loading = setInterval(function() {
		if (loaded == 0) {
			$.getScript('javascripts/fluid/app.js');
			clearInterval(loading);
		}
	}, 10);	
});
