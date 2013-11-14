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
                    baseUrl: '.',
                    appDir: 'src/FluidJS',
                    mainConfigFile: 'src/FluidJS/fluid-0.0.1.min.js',
                    findNestedDependencies: true,
                    removeCombined: true,
                    dir: 'public/javascripts',
                    modules: [
                        {
                            name: 'fluid-0.0.1.min',
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
                            ]
                        }
                    ]
                }
            }
        }
    });

    // Load tasks from NPM
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-requirejs');

    // Default task.
    grunt.registerTask('default', ['clean', 'requirejs']);
};