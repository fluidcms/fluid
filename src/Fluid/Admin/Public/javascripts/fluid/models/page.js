define(['backbone'], function (Backbone) {
	var Model = Backbone.Model.extend({	
		urlRoot: 'page',
		
		initialize: function( attrs ) {
			var obj = this;
			
			this.site = attrs.site;
			
			this.bind('newtoken', this.fetchPage);
			
			$("#website").ready(function() {
				obj.set('url', $("#website").get(0).contentWindow.location.toString());
				obj.fetchToken();
			});
			
			// Track iframe location change
			$("#website").bind('load', function() {
				obj.set('url', $("#website").get(0).contentWindow.location.toString());
				obj.fetchToken();
			});
		},
		
		reload: function() {
			$("#website")[0].contentWindow.location.reload();

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
					obj.id = response.page;
					obj.set("language", response.language);
					obj.set("page", response.page);
					obj.set("data", response.data);
					obj.set("variables", response.variables);
					obj.site.set("language", response.language);
					obj.site.set("data", response.site.data);
					obj.site.set("variables", response.site.variables);
				}});
			}});
		}
	});
		
	return {
		Model: Model
    };
});
