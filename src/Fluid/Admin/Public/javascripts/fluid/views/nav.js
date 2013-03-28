define(['backbone', 'ejs'], function (Backbone, EJS) {
	var View = Backbone.View.extend({
		template: new EJS({url: 'javascripts/fluid/templates/nav.ejs'}), 
		
		initialize: function( attrs ) {
			this.items = [
				{name: 'Structure', className: 'structure'},
				{name: 'Files', className: 'files'}
			];
			
			this.router = attrs.router;
			this.router.on("route", this.render, this);
		},
		
		render: function() {		
			this.$el.html(this.template.render({items: this.items, current: this.router.current}));
			$('#main').empty();
			$('#main').append(this.$el);
			return this;
		}
	});
	
	return View;
});