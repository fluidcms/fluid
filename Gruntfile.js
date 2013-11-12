/*global module:false*/
'use strict';

module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({

        clean: {
            release: 'public/javascripts'
        },

        requirejs: {
            compile: {
                options: {
                    baseUrl: 'src/FluidJS',
                    mainConfigFile: 'src/FluidJS/bootstrap.js',
                    paths: {
                        "jquery": "../../bower_components/jquery/jquery",
                        "jquery-ui": "../../bower_components/jquery-ui/ui/jquery-ui",
                        "backbone": "../../bower_components/backbone/backbone",
                        "underscore": "../../bower_components/underscore/underscore",
                        "async": "../../bower_components/async/lib/async",
                        "ejs": "vendor/ejs",
                        "qtip": "vendor/jquery-qtip",
                        "when": "../../bower_components/when/when",
                        "text": "../../bower_components/text/text"
                    },
                    findNestedDependencies: true,
                    removeCombined: false,
                    name: 'bootstrap',
                    exclude: [
                        'jquery',
                        'jquery-ui',
                        'backbone',
                        'underscore',
                        'async',
                        'ejs',
                        'qtip',
                        'when',
                        'text'
                    ],
                    out: 'public/javascripts/fluid-0.0.1.min.js'
                }
            }
        },

        uglify: {
            options: {
                mangle: false
            },
            my_target: {
                files: {
                    'public/javascripts/vendor/jquery-2.0.3.min.js': ['bower_components/jquery/jquery.js'],
                    'public/javascripts/vendor/jquery-ui-1.10.3.min.js': ['bower_components/jquery-ui/ui/jquery-ui.js'],
                    'public/javascripts/vendor/backbone-1.1.0.min.js': ['bower_components/backbone/backbone.js'],
                    'public/javascripts/vendor/underscore-1.5.2.min.js': ['bower_components/underscore/underscore.js'],
                    'public/javascripts/vendor/async-0.2.5.min.js': ['bower_components/async/lib/async.js'],
                    'public/javascripts/vendor/requirejs-2.1.9.min.js': ['bower_components/requirejs/require.js'],
                    'public/javascripts/vendor/when-2.5.1.min.js': ['bower_components/when/when.js'],
                    'public/javascripts/vendor/text-2.0.10.min.js': ['bower_components/text/text.js'],
                    'public/javascripts/vendor/autobahnjs-0.8.0.min.js': ['src/FluidJS/vendor/autobahnjs.js'],
                    'public/javascripts/vendor/ejs-1.0.0.min.js': ['src/FluidJS/vendor/ejs.js'],
                    'public/javascripts/vendor/jquery-qtip-2.0.1-111-nightly.min.js': ['src/FluidJS/vendor/jquery-qtip.js']
                }
            }
        },

        copy: {
            main: {
                files: [
                    {
                        expand: true,
                        cwd: 'src/FluidJS/templates/',
                        src: ['**'],
                        dest: 'public/javascripts/templates'
                    }
                ]
            }
        }
    });

    // Load tasks from NPM
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-copy');

    // Default task.
    grunt.registerTask('default', ['clean', 'requirejs', 'uglify', 'copy']);
};