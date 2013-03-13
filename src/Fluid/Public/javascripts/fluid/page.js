//
// Page model
//
var Page = Backbone.Model.extend({
	url: 'pagetoken.json',
	
	initialize: function(){
		var obj = this;
		this.fetch();
		
		setInterval(function() {
			obj.set('url', $("#website").get(0).contentWindow.location.toString());
		}, 100);
	}
});
