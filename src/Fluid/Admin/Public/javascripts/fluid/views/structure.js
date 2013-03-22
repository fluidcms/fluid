define(['backbone', 'ejs'], function (Backbone, EJS) {
	var View = Backbone.View.extend({
		className: 'structure',
		
		template: new EJS({url: 'javascripts/fluid/templates/structure/structure.ejs?'+(new Date()).getTime()}), 
	
		initialize: function() {
			this.collection.on('change reset add remove', this.render, this);
		},
		
		render: function() {
			this.$el.html(this.template.render({pages: this.collection}));
			
			$("#main").append(this.$el);
			
			return this;
		}
	});
	
	return View;
});