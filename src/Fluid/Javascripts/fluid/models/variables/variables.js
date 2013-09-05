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
            var retval = {};

            $.each(definition, function(key, item) {
                if (typeof data[key] !== 'undefined') {
                    retval[key] = root.variablesToHTML(item, data[key]);
                }
            });

           return retval;
        },

        variablesToHTML: function(definition, data) {
            var root = this;
            var retval = {};

            $.each(definition, function(key, item) {
                if (typeof data[key] !== 'undefined' && data[key] !== null) {
                    switch(item.type) {
                        case 'string':
                            retval[key] = data[key];
                            break;
                        case 'content':
                            retval[key] = root.contentToHTML(data[key]);
                            break;
                        case 'image':
                            retval[key] = root.imageToHTML(data[key]);
                            break;
                        case 'array':
                            retval[key] = [];
                            $.each(data[key], function(arrayKey, arrayItem) {
                                retval[key].push(root.variablesToHTML(item.variables, arrayItem));
                            });
                            break;
                    }
                }
            });

            return retval;
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

        toJSON: function(data, item, group) {
            var definition;
            if (typeof group !== 'undefined' && group !== null && group !== '') {
                definition = this.definition[group][item];
            } else {
                definition = this.definition[item];
            }

            return this.variableToJSON(definition, data);
        },

        variableToJSON: function(definition, data) {
            switch(definition.type) {
                case 'string':
                    // Trim and remove trailing <br> added by the editor
                    if (data === null) {
                        data = '';
                    }
                    data = data.replace(/^\s+|\s+$/g, '');
                    data = data.replace(/^<br>+|<br>+$/g, '');
                    return data;
                case 'content':
                    return this.contentToJSON(data);
                case 'image':
                    return data;
                case 'array':
                    return this.arrayToJSON(definition.variables, data);
            }
            return null;
        },

        arrayToJSON: function(definition, data) {
            var root = this;
            var retval = [];
            $.each(data, function(key, item) {
                retval[key] = {};
                if (item === null) {
                    item = {};
                }

                $.each(definition, function(name, variable) {
                    if (typeof item[name] === 'undefined') {
                        item[name] = null;
                    }
                    retval[key][name] = root.variableToJSON(variable, item[name]);
                });
            });

            return retval;
        },

        contentToJSON: function(data) {
            var output = {
                source: '',
                components: {},
                images: {}
            };

            var content = "";
            if (typeof data !== 'undefined' && data !== null && typeof data.source !== 'undefined' && data.source !== null) {
                content = data.source;
            }

            // Components
            var components = content.match(/<div id="[^"]*" data-component="[^"]*"(.|[\r\n])*?<\/div>/gi);
            if (components !== null) {
                $.each(components, function(key, value) {
                    var id = value.match(/id="([^"]*)"/)[1];
                    var component = value.match(/data-component="([^"]*)"/)[1];

                    var componentData = {};
                    if (typeof data['components'][id] !== 'undefined' && typeof data['components'][id]['data'] !== 'undefined') {
                        componentData = data['components'][id]['data'];
                    }

                    if ($.isArray(componentData) && !componentData.length) {
                        componentData = {};
                    }

                    output.components[id] = {
                        component: component,
                        data: componentData
                    };

                    content = content.replace(value, "{"+id+"}");
                });
            } else if (typeof data['components'] !== 'undefined' && data['components'] !== null) {
                $.each(data['components'], function(key, value) {
                    if (content.match(new RegExp("\{"+key+"\}"))) {
                        output.components[key] = value;
                    }
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
            } else if (typeof data['images'] !== 'undefined' && data['images'] !== null) {
                $.each(data['images'], function(key, value) {
                    if (content.match(new RegExp("\{"+key+"\}"))) {
                        output.images[key] = value;
                    }
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
