module.exports = function(grunt) {
    var package = grunt.file.readJSON('package.json');

    grunt.initConfig({
        // setting folder templates
        dirs: {
            css: 'assets/css',
            less: 'assets/less',
            js: 'assets/js'
        },

        // Compile all .less files.
        less: {
            // one to one
            core: {
                options: {
                    sourceMap: false,
                    sourceMapFilename: '<%= dirs.css %>/style.css.map',
                    sourceMapURL: 'style.css.map',
                    sourceMapRootpath: '../../'
                },
                files: {
                    '<%= dirs.css %>/style.css': '<%= dirs.less %>/style.less'
                }
            },
            admin: {
                files: {
                    '<%= dirs.css %>/admin.css': ['<%= dirs.less %>/admin.less' ]
                }
            }
        },

        // Generate POT files.
        makepot: {
            target: {
                options: {
                    exclude: ['build/.*'],
                    domainPath: '/languages/',
                    potFilename: package.slug + '.pot',
                    type: 'wp-plugin',
                    potHeaders: {
                        'report-msgid-bugs-to': 'https://wpeasysoft.com/',
                        'language-team': 'LANGUAGE <wpeasysoft@gmail.com>'
                    }
                }
            }
        },

        watch: {
            less: {
                files: '<%= dirs.less %>/*.less',
                tasks: ['less:core', 'less:admin']
            }
        },

        // Clean up build directory
        clean: {
            main: ['build/']
        },

        // Copy the plugin into the build directory
        copy: {
            main: {
                src: [
                    '**',
                    '!.git/**',
                    '!build/**',
                    '!bin/**',
                    '!Gruntfile.js',
                    '!node_modules/**',
                    '!package.json',
                    '!debug.log',
                    '!phpunit.xml',
                    '!.gitignore',
                    '!.gitmodules',
                    '!npm-debug.log',
                    '!**/Gruntfile.js',
                    '!**/package.json',
                    '!**/package-lock.json',
                    '!secret.json',
                    '!deploy.sh',
                    '!**/README.md',
                    '!assets/src/**',
                    '!assets/css/style.css.map',
                    '!tests/**',
                    '!**/*~'
                ],
                dest: 'build/'
            }
        },

        wp_readme_to_markdown: {
            your_target: {
                files: {
                    'README.md': 'readme.txt'
                }
            },
        },

        //Compress build directory into <name>.zip and <name>-<version>.zip
        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: './build/' + package.slug + '-v' + package.version + '.zip'
                },
                expand: true,
                cwd: 'build/',
                src: ['**/*'],
                dest: 'wc-return-warranty'
            }
        },

    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-contrib-concat' );
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    grunt.loadNpmTasks( 'grunt-contrib-clean' );
    grunt.loadNpmTasks( 'grunt-contrib-copy' );
    grunt.loadNpmTasks( 'grunt-contrib-compress' );
    grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );

    grunt.registerTask( 'default', [
        'less',
    ]);

    grunt.registerTask('readme', ['wp_readme_to_markdown'] );

    grunt.registerTask('release', [
        'makepot',
        'less',
        'readme'
    ]);

    grunt.registerTask( 'zip', [
        'clean',
        'copy',
        'compress'
    ]);
};
