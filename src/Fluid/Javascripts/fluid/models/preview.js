define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        initialize: function (attrs) {
            var root = this;

            this.socket = attrs.socket;
            this.languages = attrs.languages;

            /*this.site = attrs.site;

            this.bind('newtoken', this.fetchPage);

            $("#website").ready(function () {
                root.set('url', $("#website").get(0).contentWindow.location.toString());
                root.fetchToken();
            });*/

            // Track iframe location change
            $("#website").on('load', function(e) { root.verifyFrameStatus(e) });

            /*    function () {
                console.log($("#website").get(0).contentWindow.location.toString());
                //root.set('url', $("#website").get(0).contentWindow.location.toString());
                //root.fetchToken();
            });*/
        },

        verifyFrameStatus: function(e) {
            var url = $(e.target).get(0).contentWindow.location.toString();
            var session = getParameterByName(url, 'fluidsession');
            var token = getParameterByName(url, 'fluidtoken');
            var branch = getParameterByName(url, 'fluidbranch');

            if (session === '' || token === '' || branch === '') {
                this.loadPage(url);
            } else {
                console.log(url);
            }
        },

        loadPage: function(url) {
            var root = this;

            if (typeof url === 'undefined' || url === null) {
                url = "/";
            }

            this.getToken(function(response) {
                url = updateQueryStringParameter(url, 'fluidbranch', fluidBranch);
                url = updateQueryStringParameter(url, 'fluidtoken', response.token);
                url = updateQueryStringParameter(url, 'fluidsession', fluidSession);
                $("#website")[0].contentWindow.location = url;
            });
        },

        getToken: function(callback) {
            var root = this;
            this.socket.send('GET', 'token', {}, function(response) {
                callback(response);
            });
        },






        reload: function () {
            //$("#website")[0].contentWindow.location.reload(true);

        },

        fetchToken: function () {
            var root = this;
            $.getJSON('pagetoken.json', function (response) {
                root.set('token', response.token);
                root.trigger('newtoken');

                // Add client script to page // TODO doesnt really matter if its in the iframe or not
                $("body", $("#website").contents()).append($('<script>var fluidNewPageToken = "'+response.token+'";</script>'));
                $("body", $("#website").contents()).append($('<script src="/fluidcms/javascripts/fluid-client/app.js"></script>'));
            });
        },

        fetchPage: function () {
            var root = this;
            var url = updateQueryStringParameter(this.get('url'), 'fluidtoken', this.get('token'));
            $.ajax(url, {success: function (response) {
                $.ajax({
                    url: root.urlRoot,
                    dataType: "json",
                    type: "POST",
                    data: {"content": response}
                }).done(
                    function (response) {
                        root.id = response.page;
                        root.set("language", response.language);
                        root.set("page", response.page);
                        root.set("data", response.data);
                        root.set("variables", response.variables);
                        root.site.set("language", response.language);
                        root.site.set("data", response.site.data);
                        root.site.set("variables", response.site.variables);
                    }
                ).error(
                    function (XMLHttpRequest) {
                        var error = XMLHttpRequest.getResponseHeader('X-Error-Message');
                        if (error !== null) {
                            alert('An error as occured: ' + error);
                        } else {
                            alert('The connection has timed out.');
                        }
                        //location.reload();
                    }
                );
            }});
        }
    });
});
