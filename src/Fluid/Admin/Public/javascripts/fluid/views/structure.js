define(['backbone', 'ejs', 'jquery-ui', 'models/language', 'views/modal', 'views/contextmenu'], function (Backbone, EJS, jUI, Language, Modal, ContextMenu) {
	var languages = new Language;
	
	var View = Backbone.View.extend({
		events: {  
			'click a[data-action=addPage]': 'addPage',
			'click a[data-action=cancelChanges]': 'cancelChanges',
			'click a[data-action=saveChanges]': 'saveChanges',
			'contextmenu li a': 'contextmenu'
		},
	
		className: 'structure',
		
		dropbox: {},
		
		template: new EJS({url: 'javascripts/fluid/templates/structure/structure.ejs?'+(new Date()).getTime()}),  // !! Remove for production
	
		initialize: function() {
			this.collection.on('reset add remove update', this.render, this);
			this.collection.on('change', this.enableControls, this);
			this.collection.on('saved', this.disableControls, this);
		},
		
		render: function() {
			this.$el.html(this.template.render({pages: this.collection}));
			$("#main").append(this.$el);
			this.sortable();
			return this;
		},
		
		saveChanges: function() {
			this.collection.save();
		},
		
		sortable: function() {
			var obj = this;
			this.$el.find('ul.structure, ul.pages').sortable({
				update: function(event, ui) {
					var item = ui.item.attr('data-id');
					var receiver = $(event.target).parents('li').attr('data-id');
					if (typeof receiver == 'undefined') {
						receiver = '';
					}
					obj.dropbox.position = ui.item.index();
					obj.dropbox.item = item;
					obj.dropbox.receiver = receiver;
					clearTimeout(obj.dropbox.timeout);
					obj.dropbox.timeout = setTimeout(function() { obj.sort() }, 10);
				},
				axis: "y",
				connectWith: ".structureSortable",
				placeholder: "sortable-placeholder"
			});
		},
		
		sort: function() {
			this.collection.sort(this.dropbox.item, this.dropbox.receiver, this.dropbox.position);
		},
		
		enableControls: function() {
			this.$el.find('[data-action=cancelChanges],[data-action=saveChanges]').removeAttr('disabled');
		},
		
		disableControls: function() {
			this.$el.find('[data-action=cancelChanges],[data-action=saveChanges]').attr('disabled', 'true');
		},
	
		cancelChanges: function(e) {
			if(!$(e.target).is("[disabled]") && confirm('Are you sure you want to cancel changes?')) {
				this.collection.fetch({reset: true});
				this.disableControls();
			}
		},
		
		contextmenu: function(e) {
			e.preventDefault();
			var contextMenu = new ContextMenu({url: 'javascripts/fluid/templates/structure/contextmenu.ejs', parent: this, event: e}).render();
		},
		
		addPage: function() {
			new Page({ model: new this.collection.__proto__.model({parent: this.collection}), newPage: true }).render();
		},
		
		editPage: function(page) {
			new Page({ model: this.collection.findItem($(page).parents('li').attr('data-id')) }).render();
		},
		
		deletePage: function(page) {
			this.collection.removeItem($(page).parents('li').attr('data-id'));
		}
	});
	
	var Page = Modal.extend({
		events: _.extend({}, Modal.prototype.events),
		
		template: new EJS({url: 'javascripts/fluid/templates/structure/page.ejs?'+(new Date()).getTime()}),  // !! Remove for production
		
		initialize: function( attrs ) {
			if (typeof attrs.newPage !== 'undefined') {
				this.newPage = true;
			} else {
				this.newPage = false;
			}
		},
		
		renderData: function() {
			return {
				languages: languages
			};
		},
		
		submit: function() {
			if (this.newPage) {
				this.model.parent.add(this.model);
			}
		}
	});
	
	return View;
});