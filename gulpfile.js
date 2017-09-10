var gulp = require('gulp');
var browserify = require('gulp-browserify');
var uglify = require('gulp-uglify');


var src = 'webroot/src';
var dev = 'webroot/dev';
var dist = 'webroot/dist';


// imgs
gulp.task('imgsDev', function () {
    gulp.src(src + '/img/**/*')
        .pipe(gulp.dest(dev + '/img'));
});

gulp.task('imgsDist', function () {
    gulp.src(src + '/img/**/*')
        .pipe(gulp.dest(dist + '/img'));
});

// fonts
gulp.task('fontsDev', function () {
    gulp.src(src + '/fonts/**/*')
        .pipe(gulp.dest(dev + '/fonts'));
});

gulp.task('fontsDist', function () {
    gulp.src(src + '/fonts/**/*')
        .pipe(gulp.dest(dist + '/fonts'));
});

// css
gulp.task('cssDev', function () {
    gulp.src(src + '/css/**/*')
        .pipe(gulp.dest(dev + '/css'));
});

gulp.task('cssDist', function () {
    gulp.src(src + '/css/**/*')
        .pipe(gulp.dest(dist + '/css'));
});

// js
gulp.task('jsDev', function () {
    gulp.src(src + '/js/app.js')
        .pipe(browserify())
        .pipe(gulp.dest(dev + '/js'));
});

gulp.task('jsDist', function () {
    gulp.src(src + '/js/app.js')
        .pipe(browserify())
        .pipe(uglify())
        .pipe(gulp.dest(dist + '/js'));
});

gulp.task('dev', ['imgsDev', 'fontsDev', 'cssDev', 'jsDev']);
gulp.task('dist', ['imgsDist', 'fontsDist', 'cssDist', 'jsDist']);
gulp.task('dev-dist', ['dev', 'dist']);
gulp.task('dev-dist', ['dev', 'dist']);

gulp.task('watch', function () {
    gulp.watch(src + '/img/**/*', ['imgsDev', 'imgsDist']);
    gulp.watch(src + '/fonts/**/*', ['fontsDev', 'fontsDist']);
    gulp.watch(src + '/css/**/*', ['cssDev', 'cssDist']);
    gulp.watch(src + '/js/**/*', ['jsDev', 'jsDist']);
});