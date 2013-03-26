define(['backbone'], function (Backbone) {
	return Backbone.Model.extend({	
		url: 'languages.json',
		
		initialize: function() {
			this.fetch();
		}
	});
});
