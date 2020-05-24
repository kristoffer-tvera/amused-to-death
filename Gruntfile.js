module.exports = function (grunt) {

    const sass = require('node-sass');
    // configure the tasks
    var config = {
        //  Sass
        sass: {
            expanded: {
                options: {
                    implementation: sass,
                    outputStyle: 'expanded',
                    sourcemap: false,
                },
                files: {
                    'styling/site.css': 'styling/sass/main.scss',
                }
            },

            min: {
                options: {
                    implementation: sass,
                    outputStyle: 'compressed',
                    sourcemap: false
                },
                files: {
                    'styling/site.min.css': 'styling/sass/main.scss',
                }
            },
        },

        // PostCss Autoprefixer
        postcss: {
            options: {
                processors: [
                    require('autoprefixer')({overrideBrowserslist: ['last 1 version']})
                ]
            },
            expanded: {
                src: 'styling/site.css'
            },
            min: {
                src: 'styling/site.min.css'
            }
        },

        // Browser Sync integration
        browserSync: {
            bsFiles: ["script/*.js", "styling/*.css", "!**/node_modules/**/*"],
            options: {
                server: {
                    baseDir: "./" // make server from root dir
                },
                port: 8000,
                ui: {
                    port: 8080,
                    weinre: {
                        port: 9090
                    }
                },
                open: false
            }
        },

        //  Concat
        concat: {
            options: {
                separator: ';'
            },
            dist: {
                // the files to concatenate
                src: [
                    "script/partials/Globals.js",
                    "script/partials/Eventlisteners.js",
                    "script/partials/Helperfunctions.js",
                    "script/partials/Main.js"
                ],
                // the location of the resulting JS file
                dest: 'script/site.js'
            },
            temp: {
                // the files to concatenate
                options: {
                    sourceMap: true,
                    sourceMapStyle: 'link'


                },
                src: [
                    "script/partials/Globals.js",
                    "script/partials/Eventlisteners.js",
                    "script/partials/Helperfunctions.js",
                    "script/partials/Main.js"
                ],
                // the location of the resulting JS file
                dest: 'script/main.js'
            },
        },

        //  Uglify
        uglify: {
            options: {
                // Use these options when debugging
                // mangle: false,
                // compress: false,
                // beautify: true

            },
            dist: {
                files: {
                    'script/site.min.js': ['script/site.js']
                }
            }
        },

        //  Clean
        clean: {
            temp: {
                src: ['temp/']
            },
        },

        //  Watch Files
        watch: {
            sass: {
                files: ['styling/sass/**/*'],
                tasks: ['scss'],
                options: {
                    interrupt: false,
                    spawn: false,
                },
            },
            js: {
                files: ['script/**/*'],
                tasks: ['js'],
                options: {
                    interrupt: false,
                    spawn: false,
                },
            }
        },


        //  Concurrent
        concurrent: {
            options: {
                logConcurrentOutput: true,
                limit: 10,
            },
            monitor: {
                tasks: ["scss", "watch:sass", "js", "watch:js", 'server']
            },
        }
    };

    grunt.initConfig(config);

    // load the tasks
    // grunt.loadNpmTasks('grunt-gitinfo');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify-es');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-concurrent');
    grunt.loadNpmTasks('grunt-postcss');
    grunt.loadNpmTasks('grunt-browser-sync');

    // define the tasks
    grunt.registerTask(
        'release', [
            'sass:expanded',
            'sass:min',
            'postcss:expanded',
            'postcss:min',
            'concat:dist',
            'uglify:dist',
            'clean:temp'
        ]
    );

    grunt.registerTask('js', ['concat:dist', 'uglify:dist']);
    grunt.registerTask('scss', ['sass:expanded', 'sass:min']);
    grunt.registerTask('server', ['browserSync']);
    grunt.registerTask('monitor', ["concurrent:monitor"]);

};