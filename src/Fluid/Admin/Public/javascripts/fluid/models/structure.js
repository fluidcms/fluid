define(['backbone'], function (Backbone) {
	var Page = Backbone.Model.extend({
		parent: null,
		
		initialize: function( args ) {
			this.parent = args.parent;
			if (typeof args.pages != 'undefined') {
				this.set('pages', new Pages(args.pages, {parent: this}))
			} else {
				this.set('pages', new Pages([], {parent: this}))
			}
			
			if (typeof this.get('id') == 'undefined') {			
				this.set('id', this.getId());
			}
		},
		
		getId: function() {
			var parent = this.parent;
			var id = this.get('page');
			
			while(parent != null) {
				if (parent instanceof Pages) {
					parent = parent.parent;
				} else {
					id = parent.get('page') + '/' + id;
					parent = parent.parent;
				}
			}
			
			return id;
		},
		
		toJSON: function() {
			var output = _.clone(this.attributes);
			delete output.parent;
			delete output.id;
			if (this.get('pages').length) {
				output.pages = this.get('pages').toJSON();
			} else {
				delete output.pages;
			}
			return output;
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
				var parent = this;
				$.each(items, function() { this.parent = parent; });
			}
			
			this.on('all', function(e) {
				if (this.parent != null && this.parent.parent != null) {
					this.parent.parent.trigger(e);
				}
			});
		},
		
		save: function() {
			var obj = this;
			Backbone.sync('update', this, {success: function() {
				obj.trigger('saved');
			}});
		},
		
		parse: function(response) {
			var parent = this;
			$.each(response, function() {
				this.parent = parent;
				this.id = this.page;
			});
			return response;
		},
		
		sort: function(item, receiver, position) {
			var item = this.findItem(item);
			var receiver = this.findItem(receiver);
			if (typeof receiver == 'undefined') {
				receiver = this;
			} else {
				receiver = receiver.get('pages');
			}
			
			var data = item.toJSON();
			data.parent = receiver;
			
			item.parent.remove(item);
			receiver.add(data, {at: position});
			
			this.trigger('change');
		},
		
		removeItem: function(item) {
			var item = this.findItem(item);
			item.parent.remove(item);
			
			this.trigger('change');
		},
		
		findItem: function(item) {
			if (item == '') {
				return undefined;
			}
			var collection = this;
			var model;
			var items = item.split("/");
			$.each(items, function(index, value) {
				model = collection.find(function(model) { return model.get('page') == value; });
				if (typeof model.get('pages') != 'undefined') {
					collection = model.get('pages');
				}
			});
			
			return model;
		}
	});
	
	return {
		Page: Page,
		Pages: Pages
	};
});