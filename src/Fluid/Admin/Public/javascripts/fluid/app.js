Fluid.Router = Backbone.Router.extend({
	root: "/fluidcms/",
	current: "",
	
	routes: {
		"*method": "make"
	},
	
	make: function(method) {
		method = method ? method : 'structure';
		if (typeof this[method] == 'function') {
			this.current = method;
			this[method]();
		}
	},
		
	structure: function () {
		new Fluid.Structure.View({collection: new Fluid.Structure.Pages()});
	},
	
	files: function () {
		
	}
});

// Start the app
Fluid.router = new Fluid.Router();
Fluid.site = new Fluid.Model.Site();
Fluid.page = new Fluid.Model.Page();
Fluid.nav = new Fluid.View.Nav().render();
Fluid.toolbar = new Fluid.View.Toolbar().render();

// Track mouse
Fluid.mouse = {};
$(document).mousemove( function(e) {
	Fluid.mouse.x = e.pageX; 
	Fluid.mouse.y = e.pageY;
});

// Block default context menu
$(document).contextmenu(function(e) {
	e.preventDefault();
});

// Block default link behavior
Backbone.history.start({pushState: true, root: Fluid.router.root});

$(document).on('click', "a[href]", function(e) {
	var href = { prop: $(this).prop("href"), attr: $(this).attr("href") };
	var root = location.protocol + "//" + location.host + Fluid.router.root;
		
	if (href.prop && href.prop.slice(0, root.length) === root) {
		e.preventDefault();
		Backbone.history.navigate(href.attr, true);
	}
});