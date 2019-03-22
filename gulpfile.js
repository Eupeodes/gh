const gulp = require('gulp')
	, concat = require('gulp-concat')
	, rename = require('gulp-rename')
	, sourcemaps = require('gulp-sourcemaps')
	, uglify = require('gulp-uglify')
	, sass = require('gulp-sass');

function css(){
	return gulp.src(['scss/*.scss', '!scss/[a-z]_*.scss'], {sourcemaps: true})
		.pipe(sass({outputStyle: 'compressed'}))
		.pipe(rename({extname: '.min.css'}))
		.pipe(gulp.dest('public/css', {sourcemaps: '.'}));
}

function js(inFiles, outFile){
	return gulp.src(inFiles, {sourcemaps: true})
		.pipe(concat(outFile + '.js'))
		.pipe(uglify())
		.pipe(rename({extname: '.min.js'}))
		.pipe(gulp.dest('public/js/gh', {sourcemaps: '.'}));
}

function mobileJs(){
	return js(['javascript/mobile.js'], 'mobile');
}

function mapJs(){
	return js(['javascript/base.js', 'javascript/map.js'], 'map');
}

gulp.task('css', function(){
	return css();
});

gulp.task('mobileJs', function(){
	return mobileJs();
});

gulp.task('mapJs', function(){
	return mapJs();
});

gulp.task('watch', function(){
	gulp.watch('scss/*.scss', function(){
		return css();
	});
	gulp.watch('javascript/mobile.js', function(){
		return mobileJs();
	});
	gulp.watch('javascript/base.js', function(){
		return mapJs();
	});
	gulp.watch('javascript/map.js', function(){
		return mapJs();
	});
});
