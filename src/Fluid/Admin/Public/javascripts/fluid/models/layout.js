define(['backbone'], function (Backbone) {
	return Backbone.Model.extend({	
		url: 'layouts.json',
		
		initialize: function() {
			this.fetch();
		}
	});
});
