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

gulp.task('imgsDist', function() {
    gulp.src(src+'/img/**/*')
        .pipe(gulp.dest(dist+'/img'));
});

// fonts
gulp.task('fontsDev', function() {
    gulp.src(src+'/fonts/**/*')
        .pipe(gulp.dest(dev+'/fonts'));
});

gulp.task('fontsDist', function() {
    gulp.src(src+'/fonts/**/*')
        .pipe(gulp.dest(dist+'/fonts'));
});

// css
gulp.task('cssDev', function() {
    gulp.src(src+'/css/**/*')
        .pipe(gulp.dest(dev+'/css'));
});

gulp.task('cssDist', function() {
    gulp.src(src+'/css/**/*')
        .pipe(gulp.dest(dist+'/css'));
});

// js
gulp.task('jsDev', function() {
    gulp.src(src+'/js/app.js')
        .pipe(browserify())
        .pipe(gulp.dest(dev+'/js'));
});

gulp.task('jsDist', function() {
    gulp.src(src+'/js/app.js')
        .pipe(browserify())
        .pipe(gulp.dest(dist+'/js'));
});

gulp.task('dev', ['imgsDev','fontsDev','cssDev','jsDev']);
gulp.task('dist', ['imgsDist','fontsDist','cssDist','jsDist']);

gulp.task('watch', function() {
    gulp.watch(src+'/img/**/*',['imgsDev']);
    gulp.watch(src+'/fonts/**/*',['fontsDev']);
    gulp.watch(src+'/css/**/*',['cssDev']);
    gulp.watch(src+'/js/**/*',['jsDev']);
});