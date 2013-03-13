// Router
var AppRouter = Backbone.Router.extend({
	
	routes: {
		"": "structure"
	},
	
	//
	// Page
	//
	page: function() {
		this.page = new Page();
	},
	
	//
	// Toolbar
	//
	toolbar: function() {
		this.toolbar = new ToolbarView().render();
		$('#toolbar').append(this.toolbar.$el);
	},
	
	//
	// Structure page
	//
	structure: function () {
		$("#main>nav a.structure").addClass('current');
		var view = new StructureView();
		view.sections = new Sections();
		view.sections.structureView = view;
		var sectionsView = new SectionsView({collection: view.sections});
		view.sections.fetch();
		$('#main').append(view.render().$el);
		view.$el.prepend(sectionsView.$el);
	}
});

var mouseX;
var mouseY;

var app = new AppRouter();
app.page();
app.toolbar();
Backbone.history.start();

// Track mouse
$(document).mousemove( function(e) {
	mouseX = e.pageX; 
	mouseY = e.pageY;
});

// Block default context menu
$(document).contextmenu(function(e) {
	e.preventDefault();
});
