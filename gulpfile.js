var gulp = require('gulp');
var browserify = require('gulp-browserify');
var uglify = require('gulp-uglify');


var src = 'webroot/src';
var dev = 'webroot/dev';
var dist = 'webroot/dist';


// imgs
gulp.task('imgsDev', function () {
    return gulp.src(src + '/img/**/*')
        .pipe(gulp.dest(dev + '/img'));
});

gulp.task('imgsDist', function () {
    return gulp.src(src + '/img/**/*')
        .pipe(gulp.dest(dist + '/img'));
});

// fonts
gulp.task('fontsDev', function () {
    return gulp.src(src + '/fonts/**/*')
        .pipe(gulp.dest(dev + '/fonts'));
});

gulp.task('fontsDist', function () {
    return gulp.src(src + '/fonts/**/*')
        .pipe(gulp.dest(dist + '/fonts'));
});

// css
gulp.task('cssDev', function () {
    return gulp.src(src + '/css/**/*')
        .pipe(gulp.dest(dev + '/css'));
});

gulp.task('cssDist', function () {
    return gulp.src(src + '/css/**/*')
        .pipe(gulp.dest(dist + '/css'));
});

// js
gulp.task('jsDev', function () {
    return gulp.src(src + '/js/app.js')
        .pipe(browserify())
        .pipe(gulp.dest(dev + '/js'));
});

gulp.task('jsDist', function () {
    return gulp.src(src + '/js/app.js')
        .pipe(browserify())
        .pipe(uglify())
        .pipe(gulp.dest(dist + '/js'));
});

gulp.task('dev', gulp.parallel('imgsDev', 'fontsDev', 'cssDev', 'jsDev'));
gulp.task('dist', gulp.parallel('imgsDist', 'fontsDist', 'cssDist', 'jsDist'));
gulp.task('dev-dist', gulp.parallel('dev', 'dist'));

gulp.task('watch', function () {
    gulp.watch(src + '/img/**/*', gulp.parallel('imgsDev', 'imgsDist'));
    gulp.watch(src + '/fonts/**/*', gulp.parallel('fontsDev', 'fontsDist'));
    gulp.watch(src + '/css/**/*', gulp.parallel('cssDev', 'cssDist'));
    gulp.watch(src + '/js/**/*', gulp.parallel('jsDev', 'jsDist'));
});
