define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        urlRoot: window.location.rootpath + "file-upload",

        new: false,

        initialize: function (attrs, options) {
            var root = this;
            if (typeof options.file !== "undefined") {
                this.new = options.file;
            }
        },

        upload: function () {
            var root = this;
            var file = this.new;

            setTimeout(function () {
                if (file.size > 2097152) {
                    alert(file.name + ' is too big.');
                    root.collection.remove(root);
                    return;
                }

                var xhr = new XMLHttpRequest();

                root.trigger('progress', root, 0);

                // Update progress bar
                xhr.upload.addEventListener("progress", function (e) {
                    root.trigger('progress', Math.round(e.loaded / e.total * 100));
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

                            root.trigger('complete', root);
                            return;
                        }
                    }
                    alert('Unknown error uploading file.');
                    root.collection.remove(root);
                }, false);

                var data = new FormData();
                data.append('id', root.get('id'));
                data.append('topic', JSON.stringify(root.collection.socket.topic));
                data.append('file', file);

                xhr.open("POST", root.urlRoot, true);
                xhr.send(data);
            }, 100);
        }
    });
});