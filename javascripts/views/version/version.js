define(['backbone', 'ejs', 'views/helpers/modal'], function (Backbone, EJS, Modal) {
    var view = Backbone.View.extend({
        events: {
            'click [data-action="push"]': "push",
            'click [data-action="pull"]': "pull"
        },

        className: "version",

        template: new EJS({url: ' javascripts/templates/version/version.ejs' + '?' + new Date().getTime()}),

        initialize: function (attrs) {
            $("#content").before(this.$el);
            this.$el.hide();
        },

        render: function (action, data) {
            if (typeof data == 'object') {
                data = _.extend(data, {
                    action: action
                });
            } else {
                data = {action: action};
            }

            this.$el.show();
            this.$el.html(this.template.render(data));
            return this;
        },

        change: function(data) {
            switch(data.status) {
                case 'behind':
                    this.showPull(data.amount);
                    break;
                case 'ahead':
                    this.showPush();
                    break;
                case 'nothing':
                    this.hide();
                    break;
            }
        },

        hide: function() {
            this.$el.hide();
        },

        showPull: function(amount) {
            this.render('pull', {amount: amount});
        },

        showPush: function() {
            this.render('push');
        },

        push: function() {
            new Commit({version: this}).render();
        },

        pull: function() {
            this.showLoader('Downloading updates');
            $.ajax({url: fluidBranch + "/pull"}).complete(function() {
                location.reload();
            });
        },

        showLoader: function(msg) {
            this.render('loading', {msg: msg});
        }
    });

    var Commit = Backbone.View.extend($.extend({}, Modal, {
        events: $.extend({
            "keypress :input": "submit"
        }, Modal.events),

        template: new EJS({url: ' javascripts/templates/version/commit-modal.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.version = attrs.version;
        },

        submit: function(e) {
            if (e.charCode == 13 && $(e.target).val() != '') {
                this.version.showLoader('Publishing updates');
                $.ajax({url: fluidBranch + "/commit+push", type: "POST", data: {"msg": $(e.target).val()}});
                this.close();
            }
        }
    }));

    return view;
});