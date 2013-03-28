define(['backbone'], function (Backbone) {
	var File = Backbone.Model.extend({
		initialize: function( attrs, options ) {
			var root = this;
			
			this.preview = {};
			
			if (typeof options.file !== "undefined") {
				setTimeout(function() {
					root.setPreview(options.file);
					root.upload(options.file);
				}, 100);
			}
		},
		
		setPreview: function(file) {
			var root = this;
			if (file.size > 2097152) {
				alert(file.name+' is too big.');
				root.collection.remove(root);
				return;
			}
			
			var reader = new FileReader();
			reader.onload = (function (model, reader) {
				return function (e) {
					
					var tmpImg = new Image();
					tmpImg.src = reader.result;
					tmpImg.onload = function() {
						model.set('previewSrc', reader.result);
						model.setPreviewSize(tmpImg.width, tmpImg.height);
						model.collection.trigger('display', model);
					};
				};
			}(root, reader));
			reader.readAsDataURL(file);
		},
		
		setPreviewSize: function(width, height) {
			var max = 82;
			if (width > height) {
			    if (width > max) {
			    	height *= max/width;
			    	width = max;
			    }
			} else {
			    if (height > max) {
			    	width *= max/height;
			    	height = max;
			    }
			}
			this.set('previewWidth', width);
			this.set('previewHeight', height);
		},
		
		upload: function(file) {
			if (file.size > 2097152) { return; }

			var root = this;
			var xhr = new XMLHttpRequest();
			
			root.collection.trigger('progress', root, 0);
			
			// Update progress bar
			xhr.upload.addEventListener("progress", function(e) {
				root.collection.trigger('progress', root, Math.round(e.loaded/e.total*100));
			}, false);
			
			// File uploaded
			xhr.addEventListener("load", function(e) {
				if (e.target.status == 200) {
					root.collection.trigger('complete', root);					
				} else {
					root.collection.remove(root);
				}
			}, false);
			
			var data = new FormData();
			data.append('id', this.get('id'));
			data.append('file', file);
			
			xhr.open("POST", "upload", true);
			xhr.send(data);
		}
	});
	
	var Collection = Backbone.Collection.extend({
		model: File,
		
		url: 'files.json',
		
		initialize: function() {
			
		},
		
		addFile: function(file) {
			var model = new File({
				id: randomString(8), 
				name: file.name,
				size: file.size,
				type: file.type
			}, {file: file});
			
			this.add(model);			
		}
	});
	
	return {
		Model: File,
		Collection: Collection
	};
});