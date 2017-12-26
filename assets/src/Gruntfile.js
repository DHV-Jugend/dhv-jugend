module.exports = function (grunt) {
  'use strict';

  var jsHeaderFiles = [];

  var jsFooterFiles = [
    'js/general.js',
    'js/main.js'
  ];

  //noinspection JSUnresolvedFunction
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    clean: {
      dist: {
        options: {
          'force': true
        },
        src: ['dist/css/*', 'dist/js/*']
      }
    },
    sass: {
      dev: {
        options: {
          style: 'nested'
        },
        files: [{
          expand: true,
          src: ['sass/main.scss'],
          dest: '../dist/css',
          flatten: true,
          ext: '.css'
        }]
      },
      dist: {
        options: {sourcemap: 'none'},
        files: [{
          expand: true,
          src: ['sass/main.scss'],
          dest: '../dist/css',
          flatten: true,
          ext: '.css'
        }]
      }
    },
    concat: {
      jsHead: {
        src: jsHeaderFiles,
        dest: '../dist/js/header.js'
      },
      jsFooter: {
        options: {
          separator: ';\n'
        },
        src: jsFooterFiles,
        dest: '../dist/js/footer.js'
      },

      cssHead: {
        src: ['../dist/css/main.css'],
        dest: '../dist/css/header.css'
      }
    },
    postcss: {
      options: {
        map: true,
        processors: [
          require('autoprefixer')({
            browsers: [
              // Use bootstrap browsers (but exclude IE8)
              'Android 2.3',
              'Android >= 4',
              'Chrome >= 20',
              'Firefox >= 24',
              'Explorer >= 9',
              'iOS >= 6',
              'Opera >= 12',
              'Safari >= 6'
            ]
          })
        ]
      },
      dist: {
        src: '../dist/css/*.css'
      }
    },
    uglify: {
      dist: {
        files: {
          '../dist/js/header.js': ['../dist/js/header.js'],
          '../dist/js/footer.js': ['../dist/js/footer.js']
        }
      },
      options: {
        report: 'min',
        mangle: false,
        screwIE8: true
      }
    },
    cssmin: {
      dist: {
        options: {
          shorthandCompacting: true,
          keepSpecialComments: 0
        },
        files: [{
          expand: true,
          cwd: '../dist/css',
          src: ['*.css'],
          dest: '../dist/css',
          ext: '.css'
        }]
      }
    },
    copy: {
      dist: {
        files: [
          {
            expand: true,
            src: ['fonts/**', 'img/**'],
            dest: '../dist/'
          }
        ]
      }
    },
    watch: {
      css: {
        files: ['css/**/*.css'],
        tasks: ['concat:cssHead']
      },
      sass: {
        files: ['sass/**/*.scss'],
        tasks: ['sass:dev', 'concat:cssHead']
      },
      js: {
        files: ['js/**/*.js'],
        tasks: ['concat']
      },
      fonts: {
        files: ['fonts/**'],
        tasks: ['copy']
      },
      img: {
        files: ['img/**'],
        tasks: ['copy']
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-postcss');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('default', ['clean', 'sass:dev', 'concat', 'copy']);
  grunt.registerTask('dist', ['clean', 'sass:dist', 'concat', 'uglify', 'postcss', 'cssmin', 'copy']);
};