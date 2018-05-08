var gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    notify = require('gulp-notify'),
    imagemin = require('gulp-imagemin'),
    pngquant = require('imagemin-pngquant'),
    filter       = require('gulp-filter'),
    concat = require('gulp-concat'),
    order = require("gulp-order"),
    size = require('gulp-size'),
    browserSync = require('browser-sync'),
    reload = browserSync.reload,
    psi = require('psi'),
    site = '',
    key = '';

var config = {
    sassFiles: './sass/*.scss',
    sassPath: './sass/style.scss',
    bowerDir: './bower_components'
}

// browser-sync task for starting the server.
gulp.task('browser-sync', function() {
    browserSync({
        server: {
            baseDir: "./"
        }
    });
});

//fonts
gulp.task('fonts', function() {
    return gulp.src(config.bowerDir + '/bootstrap-sass/assets/fonts/**/*')
    .pipe(gulp.dest('./fonts'))
    .pipe(notify({ message: 'Fonts task complete' }))
    
});

//sass
gulp.task('sass', function() {
  return gulp.src(config.sassPath)
    .pipe(sass({
      style: 'compressed',
      includePaths: [config.bowerDir + '/bootstrap-sass/assets/stylesheets'],
      loadPath: [ 
        './sass',
        config.bowerDir + '/bootstrap-sass/assets/stylesheets'
       ]
    }))
    .on("error", notify.onError(function (error) {
                 return "Error: " + error.message;
     }))
    .pipe(autoprefixer({
        browsers: [  
            "Android 2.3",
            "Android >= 4",
            "Chrome >= 20",
            "Firefox >= 24",
            "Explorer >= 8",
            "iOS >= 6",
            "Opera >= 12",
            "Safari >= 6"
        ]
    }))
    .pipe(minifycss())
    .pipe(gulp.dest('./'))
    .pipe(size())
    .pipe(notify({ message: 'Styles task complete' }))
    .pipe(reload({stream:true}));
});

//js
gulp.task('js', function() {
  return gulp.src(['js/ui/*.js', 'js/main.js'])
    .pipe(order([
        "js/ui/*.js",
        "js/main.js"
    ]))
    .pipe(jshint())
    .pipe(jshint.reporter('default'))
    .pipe(concat('main.min.js'))
    .pipe(uglify()) 
    .pipe(gulp.dest('js'))
    .pipe(size())
    .pipe(notify({ message: 'Scripts task complete' }));
});
//img
gulp.task('img', function () {
    return gulp.src('img/dev/*')
        .pipe(imagemin({
            progressive: true,
            svgoPlugins: [{removeViewBox: false}],
            use: [pngquant()]
        }))
        .pipe(gulp.dest('img/'))
        .pipe(size())
        .pipe(notify({ message: 'img optimized' }));
});
//svg


// psi 
// gulp.task('mobile', function (cb) {
//     psi({
//         // key: key
//         nokey: 'true',
//         url: site,
//         strategy: 'mobile',
//     }, cb);
// });
// gulp.task('desktop', function (cb) {
//     psi({
//         nokey: 'true',
//         // key: key,
//         url: site,
//         strategy: 'desktop',
//     }, cb);
// });

// run all tasks
gulp.task('default', function() {
    gulp.start('sass', 'js', 'img', 'fonts');
});
//run gulp tasks on file change
gulp.task('watch', function() {
  gulp.watch('sass/**/*.scss', ['sass']);
  gulp.watch('js/main.js', ['js']);
});
