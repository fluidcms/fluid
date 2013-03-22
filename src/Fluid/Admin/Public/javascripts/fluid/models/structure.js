Fluid.Structure.Page = Backbone.Model.extend({
	initialize: function( args ) {
		if (typeof args.pages != 'undefined') {
			this.set('pages', new Fluid.Structure.Pages(args.pages, {parent: this}))
		}
	}
});

Fluid.Structure.Pages = Backbone.Collection.extend({
	model: Fluid.Structure.Page,
	
	url: 'structure.json',
	
	parent: null,
	
	initialize: function( items, args ) {
		if (this.parent == null && (typeof args == 'undefined' || typeof args.parent == 'undefined')) {
			this.fetch();
		} else {
			this.parent = args.parent;
		}
		//var obj = this;
		//setInterval(function() { obj.fetch(); }, 1000);
	},
	
	parse: function(response) {
		var parent = this;
		$.each(response, function() {
			this.parent = parent;
		});
		return response;
	}
});
