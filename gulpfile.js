const gulp = require('gulp');
const browserify = require('gulp-browserify');


const src = 'webroot/src';
const dev = 'webroot/dev';
const dist = 'webroot/dist';



// imgs
gulp.task('imgsDev', function() {
    gulp.src(src+'/img/**/*')
        .pipe(gulp.dest(dev+'/img'));
});

// fonts
gulp.task('fontsDev', function() {
    gulp.src(src+'/fonts/**/*')
        .pipe(gulp.dest(dev+'/fonts'));
});

// css
gulp.task('cssDev', function() {
    gulp.src(src+'/css/**/*')
        .pipe(gulp.dest(dev+'/css'));
});

// js
gulp.task('jsDev', function() {
    gulp.src(src+'/js/app.js')
        .pipe(browserify())
        .pipe(gulp.dest(dev+'/js'));
});

gulp.task('dev', ['imgsDev','fontsDev','cssDev','jsDev']);

gulp.task('watch', function() {
    gulp.watch(src+'/img/**/*',['imgsDev']);
    gulp.watch(src+'/fonts/**/*',['fontsDev']);
    gulp.watch(src+'/css/**/*',['cssDev']);
    gulp.watch(src+'/js/**/*',['jsDev']);
});