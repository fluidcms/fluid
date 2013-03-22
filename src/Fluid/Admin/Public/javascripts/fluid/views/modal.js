var ModalWindow = Backbone.View.extend({
	tagName: 'div',
	
	className: "modal-container",
	
	events: {
		"click [data-action=close]": "close",
		"submit form": "submit"
	},
	
	initialize: function ( config ) {
		this.parent = config.parent;
		this.template = new EJS({url: 'templates/'+config.url});
	},
	
	render: function() {
		var obj = this;
		
		this.$el.html('<div class="modal-window">'+this.template.render()+'</div>');
		$(document.body).append(this.$el);
		$(document.body).addClass('blur');
		
		// Escape key will close the modal
		setTimeout(function() {
			$(document).keyup(function(e) {
				if (e.keyCode == 27) obj.close();
			});

		}, 1);
		
		// Custom validation
		$.each(this.$el.find('form input[data-validation]'), function() {
			obj.validateInput(this);
			$(this).bind('input', function() { obj.validateInput($(this)) });
		});
		
		this.$el.find('form input:first').focus();
		
		return this;
	},
	
	validateInput: function( input ) {
		if (typeof this.model.validate !== undefined && typeof this.model.validate[$(input).attr('data-validation')] === 'function') {
			$(input)[0].setCustomValidity(
				this.model.validate[$(input).attr('data-validation')](this.model, input)
			);
		}
	},
	
	close: function() {
		this.remove();
		$(document.body).removeClass('blur');
	},
	
	submit: function(e) {
		e.preventDefault();
		if (typeof this.model.submit === 'function') {

			var o = {};
			var a = $(e.target).serializeArray();
			$.each(a, function() {
				if (o[this.name] !== undefined) {
					if (!o[this.name].push) {
						o[this.name] = [o[this.name]];
					}
					o[this.name].push(this.value || '');
				} else {
					o[this.name] = this.value || '';
				}
			});

			this.model.submit(this.model, o);
		}
		this.close();
	}
});
