define(['backbone', 'ejs'], function (Backbone, EJS) {
    var View = Backbone.View.extend({
        language: '',

        events: {
            'click a[data-action=preview]': 'previewPage',
            'click a[data-action=edit]': 'editPage',
            'click a[data-action=reload]': 'reloadPage'
        },

        template: new EJS({url: 'javascripts/fluid/templates/toolbar.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.languages = attrs.languages;

            this.languages.on('change', this.render, this);
        },

        render: function () {
            var language;
            if (typeof this.languages !== 'undefined' && this.languages.current !== null) {
                language = this.languages.current.get('language');
            }

            this.$el.html(this.template.render({
                languages: this.languages,
                language: language
            }));
            $('#toolbar').append(this.$el);
            return this;
        },

        previewPage: function (e) {
            this.$el.find('.toolbar-button').removeClass('current');
            $("a[data-action=preview]", this.$el).addClass('current');

            if (typeof this.pageEditor !== 'undefined') {
                this.pageEditor.remove();
            }
        },

        editPage: function (e) {
            var root = this;

            $(e.target).parent().find('.toolbar-button').removeClass('current');
            $(e.target).addClass('current');

            require(['views/pageeditor'], function (PageEditorView) {
                root.pageEditor = new PageEditorView({page: root.page, site: root.site, toolbar: root}).render();
            });
        },

        reloadPage: function(e) {
            this.page.reload();
        },

        changeLanguage: function (model) {
            if (model.get('language') !== undefined && model.get('language') !== this.language) {
                this.language = model.get('language');
                this.render();
            }
        }
    });

    return View;
});