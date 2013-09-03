define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        image:  '<img src="%src" alt="" id="%id">',
        component: '<div id="%id" data-component="%component" contenteditable="false" class="component" style=""><img src="%src" width="54" height="32" alt="">%name</div>',

        definition: null,
        data: null,

        initialize: function (attrs, options) {
            this.components = options.components;
            this.definition = options.definition;
            this.data = options.data;
        },

        updateData: function(data) {
            this.data = data;
        },

        toJSON: function(data, item, group) {
            var definition;
            if (typeof group !== 'undefined' && group !== null && group !== '') {
                definition = this.definition[group][item];
            } else {
                definition = this.definition[item];
            }

            var output;
            switch(definition.type) {
                case 'string':
                    // Trim and remove trailing <br> added by the editor
                    data = data.replace(/^\s+|\s+$/g, '');
                    data = data.replace(/^<br>+|<br>+$/g, '');
                    output = data;
                    break;
                case 'content':
                    output = this.contentToJSON(data);
                    break;
                case 'image':
                    output = data;
                    break;
            }

            return output;
        },

        toHTML: function() {
            var groups = false;
            for(var key in this.definition) {
                if (typeof this.definition[key]['type'] === 'undefined') {
                    groups = true;
                }
                break;
            }

            if (groups) {
                return this.groupsToHTML(this.definition, this.data);
            } else {
                return this.variablesToHTML(this.definition, this.data);
            }
        },

        groupsToHTML: function(definition, data) {
            var root = this;
            var output = {};

            $.each(definition, function(key, item) {
                if (typeof data[key] !== 'undefined') {
                    output[key] = root.variablesToHTML(item, data[key]);
                }
            });

           return output;
        },

        variablesToHTML: function(definition, data) {
            var root = this;
            var output = {};

            $.each(definition, function(key, item) {
                if (typeof data[key] !== 'undefined' && data[key] !== null) {
                    switch(item.type) {
                        case 'string':
                            output[key] = data[key];
                            break;
                        case 'content':
                            output[key] = root.contentToHTML(data[key]);
                            break;
                        case 'image':
                            output[key] = root.imageToHTML(data[key]);
                            break;
                    }
                }
            });

            return output;
        },

        imageToHTML: function(content) {
            var width = parseInt(typeof content.width !== 'undefined' ? content.width : 0);
            var height = parseInt(typeof content.height !== 'undefined' ? content.height : 0);
            var src = content.src;

            if (width === 0 && height === 0) {
                $.each(content, function(formatkey, format) {
                    if (typeof format.width !== 'undefined' && parseInt(format.width) > width) {
                        width = parseInt(format.width);
                        height = parseInt(typeof format.height !== 'undefined' ? format.height : "");
                        src = format.src;
                        if (width !== 0 || height !== 0) {
                            return false;
                        }
                    }
                });
            }

            return '<img src="'+src+'" width="" height="" alt="">';

        },

        contentToHTML: function(content) {
            var root = this;
            var output = content.source;

            // Components
            if (typeof content.components !== 'undefined' && content.components !== null) {
                $.each(content.components, function(key, item) {
                    var component = root.components.findWhere({component: item.component});
                    var html = root.component
                        .replace('%id', key)
                        .replace('%src', 'data:image/jpg;base64,' + component.get('icon'))
                        .replace('%component', component.get('component'))
                        .replace('%name', component.get('name'));
                    output = output.replace('{'+key+'}', html);
                });
            }

            // Images
            if (typeof content.images !== 'undefined' && content.images !== null) {
                $.each(content.images, function(key, item) {
                    var html = root.image.replace('%src', item.src).replace('%id', key);
                    output = output.replace('{'+key+'}', html);
                });
            }

            return output;
        },

        contentToJSON: function(data) {
            var output = {
                source: '',
                components: {},
                images: {}
            };

            var content = data.source;

            // Components
            var components = content.match(/<div id="[^"]*" data-component="[^"]*"(.|[\r\n])*<\/div>/gi);

            if (components !== null) {
                $.each(components, function(key, value) {
                    var id = value.match(/id="([^"]*)"/)[1];
                    var component = value.match(/data-component="([^"]*)"/)[1];

                    var componentData = {};
                    if (typeof data['components'][id] !== 'undefined' && typeof data['components'][id]['data'] !== 'undefined') {
                        componentData = data['components'][id]['data'];
                    }

                    output.components[id] = {
                        component: component,
                        data: componentData
                    };

                    content = content.replace(value, "{"+id+"}");
                });
            }

            // Images
            var images = content.match(/<img .+>/gi);
            if (images !== null) {
                $.each(images, function(key, value) {
                    var id = value.match(/id="([^"]*)"/)[1];

                    output.images[id] = {
                        src: value.match(/src="([^"]*)"/)[1],
                        alt: "",
                        width: "",
                        height: ""
                    };

                    content = content.replace(value, "{"+id+"}");
                });
            }

            content = content.replace(/^\s+|\s+$/g, '')
                .replace(/[\r\n]\s*([\r\n])/g, '$1')
                .replace(/([\r\n])\s*/g, '$1')
                .replace(/\s*([\r\n])/g, '$1');

            output.source = content;
            return output;
        }
    });
});
