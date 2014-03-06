define(['backbone'], function (Backbone) {
    var File = Backbone.Model.extend({
        urlRoot: "file",

        new: false,

        initialize: function (attrs, options) {
            var root = this;

            this.socket = attrs.socket;
            this.set('preview', {});

            if (typeof attrs.width !== 'undefined' && attrs.width !== null) {
                this.setPreviewSize(attrs.width, attrs.height);
            }

            if (typeof options.file !== "undefined") {
                this.new = options.file;
                // Start uploading the file
                setTimeout(function () { root.upload(options.file); }, 100);
            }
        },

        getPreview: function() {
            var root = this;

            if (typeof this.new === 'undefined' || !this.new) {
                this.socket.send('GET', this.urlRoot + "/preview/" + this.id, {}, function(response) {
                    var preview = root.get('preview');
                    preview.image = 'data:image/jpg;base64,'+response.image;
                    root.set('preview', preview);
                    root.trigger('preview');
                });
            } else {
                var reader = new FileReader();
                reader.onload = (function (model, reader) {
                    return function (e) {
                        var tmpImg = new Image();
                        tmpImg.src = reader.result;
                        tmpImg.onload = function () {
                            model.setPreviewSize(tmpImg.width, tmpImg.height);
                            var preview = model.get('preview');
                            preview.image = reader.result;
                            model.set('preview', preview);
                            model.trigger('preview');
                        };
                    };
                }(root, reader));
                reader.readAsDataURL(this.new);
            }
        },

        setPreviewSize: function (width, height) {
            var max = 82;
            if (width > height) {
                if (width > max) {
                    height *= max / width;
                    width = max;
                }
            } else {
                if (height > max) {
                    width *= max / height;
                    height = max;
                }
            }
            var preview = this.get('preview');
            preview.width = width;
            preview.height = height;

            this.set('preview', preview);
        },

        upload: function (file) {
            var root = this;

            if (file.size > 2097152) {
                alert(file.name + ' is too big.');
                root.collection.remove(root);
                return;
            }

            var xhr = new XMLHttpRequest();

            root.collection.trigger('progress', root, 0);

            // Update progress bar
            xhr.upload.addEventListener("progress", function (e) {
                root.collection.trigger('progress', root, Math.round(e.loaded / e.total * 100));
            }, false);

            // File uploaded
            xhr.addEventListener("load", function (e) {
                if (e.target.status == 200) {
                    var response = $.parseJSON(e.target.response);
                    if (typeof response.id !== 'undefined') {

                        root.set({
                            'src': response.src,
                            'name': response.name,
                            'width': response.width,
                            'height': response.height,
                            'type': response.type,
                            'size': response.size,
                            'creation': response.creation
                        });

                        root.collection.trigger('complete', root);
                        return;
                    }
                }
                alert('Unknown error uploading file.');
                root.collection.remove(root);
            }, false);

            var data = new FormData();
            data.append('id', this.get('id'));
            data.append('topic', JSON.stringify(root.collection.socket.topic));
            data.append('file', file);

            xhr.open("POST", root.urlRoot, true);
            xhr.send(data);
        }
    });

    return Backbone.Collection.extend({
        model: File,

        url: 'files',

        initialize: function (items, attrs) {
            this.socket = attrs.socket;
        },

        fetch: function () {
            var root = this;
            this.socket.send('GET', this.url, {}, function(response) {
                root.parse(response);
            });
        },

        parse: function(response) {
            var root = this;
            $.each(response, function() {
                this.socket = root.socket;
            });
            this.reset(response);
        },

        comparator: function (file) {
            return file.get('creation') * -1;
        },

        addFile: function (file) {
            var model = new File({
                id: randomString(8),
                name: file.name,
                size: file.size,
                type: file.type,
                creation: Math.round((new Date()).getTime() / 1000)
            }, {file: file});

            this.add(model);
        }
    });
});