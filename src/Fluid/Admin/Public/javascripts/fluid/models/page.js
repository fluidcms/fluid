Fluid.Model.Page = Backbone.Model.extend({	
	initialize: function(){
		var obj = this;
		
		this.bind('newtoken', this.fetchPage);
		
		// Track iframe location change
		$("#website").bind('load', function() {
			obj.set('url', $("#website").get(0).contentWindow.location.toString());
			obj.fetchToken();
		});
	},
	
	fetchToken: function() {
		var obj = this;
		$.getJSON('pagetoken.json', function(response) {
			obj.set('token', response.token);
			obj.trigger('newtoken');
		});
	},
	
	fetchPage: function() {
		var obj = this;
		var url = updateQueryStringParameter(this.get('url'), 'fluidtoken', this.get('token'));
		$.ajax(url, {success: function(response) {
			$.ajax('page.json', {dataType: "json", type: "post", data: {"content": response}, success: function(response) {
				obj.set("language", response.language);
				obj.set("page", response.page);
				obj.set("data", response.data);
				obj.set("variables", response.variables);
				Fluid.site.set("data", response.site.data);
				Fluid.site.set("variables", response.site.variables);
			}});
		}});
	}
});

function updateQueryStringParameter(uri, key, value) {
	var re = new RegExp("([?|&])" + key + "=.*?(&|$)", "i");
	separator = uri.indexOf('?') !== -1 ? "&" : "?";
	if (uri.match(re)) {
		return uri.replace(re, '$1' + key + "=" + value + '$2');
	} else {
		return uri + separator + key + "=" + value;
	}
}