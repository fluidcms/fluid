define(['backbone', 'ejs'], function (Backbone, EJS) {
	var View = Backbone.View.extend({
		template: new EJS({url: 'javascripts/fluid/templates/nav.ejs'}), 
		
		initialize: function( args ) {
			this.items = [
				{name: 'Structure', className: 'structure'},
				{name: 'Files', className: 'files'}
			];
			
			this.router = args.router;
			this.router.on("route", this.render, this);
		},
		
		render: function() {		
			this.$el.html(this.template.render({items: this.items, current: this.router.current}));
			$('#main').append(this.$el);
			return this;
		}
	});
	
	return View;
});