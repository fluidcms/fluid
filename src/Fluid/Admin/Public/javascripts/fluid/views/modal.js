define(['backbone', 'ejs'], function (Backbone, EJS) {	
	var Modal = Backbone.View.extend({
		className: 'modal-container',
		
		events: {
			"click [data-action=close]": "close",
			'change form': 'change',
			"submit form": "submitForm"
		},
		
		render: function() {
			var obj = this;
											
			this.$el.html('<div class="modal-window">'+this.template.render(_.extend({model: this.model}, this.renderData()))+'</div>');
			
			$(document.body).append(this.$el);
			$(document.body).addClass('blur');
			
			// Escape key will close the modal
			setTimeout(function() {
				$(document).keyup(function(e) {
					if (e.keyCode == 27) {
						obj.close();
					}
				});
			}, 1);
						
			this.$el.find('input:first').focus();
			
			this.$el.find('form').trigger('change');
			
			if (typeof this.afterRender === 'function') {
				this.afterRender();
			}
			
			return this;
		},
				
		close: function() {
			this.remove();
			$(document.body).removeClass('blur');
		},
		
		change: function(e) {			
			var data = {};
			$.each($(e.currentTarget).serializeArray(), function(key, item) {
				if (item.name.match(/\[\]$/)) {
					item.name = item.name.substr(0, item.name.length-2);
					if (typeof data[item.name] !== 'object') {
						data[item.name] = [];
					}
					data[item.name][data[item.name].length] = item.value;
				} else {
					data[item.name] = item.value;
				}
			});
			this.model.set(data, {validate: true});
			$.each($(e.currentTarget).find("[name="+this.model.validationErrorAttr+"]"), function() { this.setCustomValidity(''); });
			if (this.model.validationError !== null) {
				$(e.currentTarget).find("[name="+this.model.validationErrorAttr+"]")[0].setCustomValidity(this.model.validationError);
			}
		},
		
		submitForm: function(e) {
			e.preventDefault();
			
			if (this.model.validationError === null) {
				if (typeof this.submit === 'function') {
					this.submit();
				}
				this.close();
			}
		}
	});
	
	return Modal;
});
