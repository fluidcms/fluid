define(['backbone'], function (Backbone) {
	var Page = Backbone.Model.extend({
		initialize: function( args ) {
			if (typeof args.pages != 'undefined') {
				this.set('pages', new Pages(args.pages, {parent: this}))
			}
		}
	});
	
	var Pages = Backbone.Collection.extend({
		model: Page,
		
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
	
	return {
		Page: Page,
		Pages: Pages
	};
});