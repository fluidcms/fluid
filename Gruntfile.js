/*global module:false*/
'use strict';

module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({

        clean: {
            dist: ['public/javascripts'],
            build: ['src/FluidJS']
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
        },

        copy: {
            src: {
                files: [
                    {
                        expand: true,
                        cwd: 'src/FluidJS/',
                        src: ['**'],
                        dest: 'public/javascripts'
                    }
                ]
            },
            dist: {
                files: [
                    {
                        expand: true,
                        cwd: 'public/javascripts/',
                        src: ['**'],
                        dest: 'src/FluidJS'
                    }
                ]
            }
        }
    });

    // Load tasks from NPM
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('develop', [
        'clean:dist',
        'copy:src'
    ]);

    grunt.registerTask('build', [
        'clean:build',
        'copy:dist',
        'clean:dist',
        'requirejs'
    ]);

    grunt.registerTask('default', [
        'clean:dist',
        'requirejs'
    ]);
};