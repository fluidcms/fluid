//
// Section models
//
var Section = Backbone.Model.extend({
	initialize: function(){
		this.set('pages', new Pages(this.get('pages')));
	}
});

var Sections = Backbone.Collection.extend({
	model : Section, 
	
	url: 'structure.json',
	
	initialize: function() {
		this.bind("remove add", function(){
			this.structureView.allowChanges();
		});		
	},
	
	validate: {
		sectionName: function(collection, input) {
			var value = $.trim($(input).val().toString());
			if (!/\S/.test(value)) {
				return 'Section Name is empty.';
			} else if ($.inArray(value, collection.pluck('name')) > -1) {
				return 'Section Name is already in use.';
			}
			return '';
		}
	},
	
	submit: function(collection, data) {
		collection.add(data);
/* /		console.log(collection,data); */
	}
});


//
// Page models
//
var Page = Backbone.Model.extend({
	initialize: function(){
		this.set('pages', new Pages(this.get('pages')));
	}
});

var Pages = Backbone.Collection.extend({
	model: Page
});


//
// Structure views
//
var StructureView = Backbone.View.extend({
	events: {  
		'click a[data-action=addSection]': 'addSection',
		'click a[data-action=cancelChanges]': 'cancelChanges',
		'click a[data-action=saveChanges]': 'saveChanges'
	},
	
	className: "structure",
		
	template: new EJS({url: 'templates/structure/structure.ejs'}), 
		
	render: function() {
		this.$el.html(this.template.render(this.model));
		
		return this;
	},
	
	addSection: function() {
		var modalWindow = new ModalWindow({url: 'structure/add-section.ejs', parent: this, model: this.sections}).render();
	},
	
	cancelChanges: function(e) {
		if(!$(e.target).is("[disabled]") && confirm('Are you sure you want to cancel changes?')) {
			this.sections.fetch();
			this.blockChanges();
		}
	},
	
	saveChanges: function(e) {
		if(!$(e.target).is("[disabled]")) {
			console.log(this.sections);
			this.blockChanges();
		}
	},
	
	allowChanges: function() {
		this.$el.find('[data-action=cancelChanges],[data-action=saveChanges]').removeAttr('disabled');
	},
	
	blockChanges: function() {
		this.$el.find('[data-action=cancelChanges],[data-action=saveChanges]').attr('disabled', 'true');
	}
});


//
// Section views
//
var SectionView = Backbone.View.extend({
	tagName: 'li',
	
	events: {  
		'contextmenu >a': 'contextmenu',
		'drop >' : 'drop'
	},
	
	template: new EJS({url: 'templates/structure/section.ejs'}), 
		
	render: function() {
		this.$el.html(this.template.render(this.model));
		
		if (this.model.get('pages')) {
			this.pagesView = new PagesView({collection: this.model.get('pages')});
			this.pagesView.parent = this;
			$(this.$el).append(this.pagesView.render().$el);
		}
		
		return this;
	},
	
	destroy: function() {
		this.model.collection.remove(this.model);
		this.remove();
	},
	
	drop: function(event, index) {
		//console.log(this.parentView);
		//this.$el.trigger('update-sort', [this.model, index]);
	},
	
	contextmenu: function(e) {
		e.preventDefault();
		var contextMenu = new ContextMenu({url: 'structure/context-menu.ejs', parent: this}).render();
	},
	
	deleteItem: function() {
		if(confirm("Are you sure you want to delete "+this.model.get('name')+"?")) {
			this.destroy();
		}
	}
});

var SectionsView = Backbone.View.extend({
	events: {
		'update-sort >': 'updateSort'		
	},
	
	tagName: 'ul',
	
	className: "sections",
		
	initialize: function () {
		this.collection.bind('reset add', this.render, this); 
	},
	
	appendModelView: function(model) {
		var el = new SectionView({model: model}).render().$el;
		this.$el.append(el);
	},
	
	render: function() {
		this.$el.children().remove();
		this.collection.each(this.appendModelView, this);
		this.sortable();
		
		return this;
	},
	
	sortable: function() {
		this.$el.sortable({
			stop: function(event, ui) {
				console.log('dropped section');
				//ui.item.trigger('drop', ui.item.index());
			},
			axis: "y",
			items: ">li",
			start: function( e, ui ) {
			},
			placeholder: "sortable-placeholder"
		});
	},
	
	updateSort: function(event, model, position) {
		console.log('updaqte sections');
/*		this.collection.remove(model);
		
		this.collection.each(function (model, index) {
			var ordinal = index;
			if (index >= position)
				ordinal += 1;
			model.set('ordinal', ordinal);
		});			
		
		model.set('ordinal', position);
		this.collection.add(model, {at: position});
				
		this.render();*/
	}
});


//
// Page views
//
var PageView = Backbone.View.extend({
	events: {  
		'contextmenu >a': 'contextmenu',
		'drop': 'drop', 
	},
	
	tagName: 'li',
		
	template: new EJS({url: 'templates/structure/pages.ejs'}), 
	
	initialize: function ( config ) {
		this.parentView = config.parentView;
	},
	
	render: function() {
		this.$el.html(this.template.render(this.model));
		
		if (this.model.get('pages')) {
			this.pagesView = new PagesView({collection: this.model.get('pages')});
			this.pagesView.parent = this;
			$(this.$el).append(this.pagesView.render().$el);
		}
				
		return this;
	},
	
	drop: function(event, item) {
		console.log(this.parentView.$el);
		/*var isItem = false;
		$.each(this.parentView.$el.find('> li'), function() {
			console.log(this, item, 'noinoi');
			if (this == item) {
				isItem = true;
			}
		});
		if (isItem) this.parentView.updateSort(this.model, $(item).index());*/
	},
	
	contextmenu: function(e) {
		e.preventDefault();
		var contextMenu = new ContextMenu({url: 'structure/context-menu.ejs', parent: this}).render();
	}
});

var PagesView = Backbone.View.extend({	
	events: {  
		'change-collection': 'changeCollection',
		'update-collection': 'updateCollection'
	},
	
	tagName: 'ul',
	
	className: "pages",
		
	initialize: function () {
		this.collection.bind('reset', this.render, this);
	},
	
	appendModelView: function(model) {
		var el = new PageView({model: model, parentView: this}).render().$el;
		this.$el.append(el);
	},
	
	render: function() {
		this.$el.children().remove();
		this.collection.each(this.appendModelView, this);
		this.sortable();
		
		return this;
	},
		
	sortable: function() {
		this.$el.sortable({
			receive: function(event, ui) {
				$(ui.sender).trigger('change-collection', {sender: ui.sender, receiver: event.target, item: ui.item});
			},
			update: function(event, ui) {
				if (!ui.sender && this === ui.item.parent()[0]) {
					ui.item.trigger('update-collection', ui.item.index());
				}
			},
/*			stop: function(e, ui) {
				console.log(ui);
				ui.item.trigger('drop', ui.item);
			},*/
			axis: "y",
			items: ">li",
			start: function( e, ui ) {
			},
			connectWith: "ul.pages",
			placeholder: "sortable-placeholder"
		});
	},
	
	changeCollection: function(e, elements) {
		console.log('enter here');
		// Remove model from sender
		var model = this.collection.get(elements.item.find('a').attr('data-id'));
		if (typeof model == 'object') {
			PagesDropbox = model;
			this.collection.remove(model);
			this.render();
			console.log('send');

		// Add model to receiver
		} //else {
			var obj = this;
			console.log('try receive');
			$.each($(elements.receiver).find('>li>a[data-id]'), function() {
				if($(this).attr('data-id') == elements.item.find('a').attr('data-id')) {
					obj.collection.add(PagesDropbox);
					obj.render();
					//console.log('yo');
					//console.log(obj.collection);
					console.log('receive');
				}
			});
		//}
	},
	
	updateCollection: function(e, position) {
		var model = this.collection.get($(e.target).find('a').attr('data-id'));
		
		if(typeof model != 'undefined') {		
			this.collection.remove(model);
			
			this.collection.each(function (model, index) {
				var ordinal = index;
				if (index >= position)
					ordinal += 1;
				model.set('ordinal', ordinal);
			});			
			
			model.set('ordinal', position);
			this.collection.add(model, {at: position});
					
			this.render();
		}
	}
	
});

var PagesDropbox;