define(['backbone', 'ejs', 'jquery-ui'], function (Backbone, EJS, jUI) {
    return Backbone.View.extend({
        events: {
        },

        className: 'components',

        template: new EJS({url: ' javascripts/templates/components/components.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.collection.on('reset', this.render, this);
        },

        render: function () {
            this.$el.html(this.template.render({components: this.collection}));
            $("#main #content").append(this.$el);
            this.draggable();
            return this;
        },

        hide: function() {
            this.$el.hide();
        },

        show: function() {
            this.$el.show();
        },

        draggable: function() {
            this.$el.find('ul.components a').draggable({
                connectToSortable: "div[contenteditable]",
                helper: "clone",
                containment: "document",
                revert: "invalid",
                iframeFix: true
            });
        }
    });
});