const gulp = require('gulp')
	, concat = require('gulp-concat')
	, rename = require('gulp-rename')
	, uglify = require('gulp-uglify')
	, sass = require('gulp-sass')
	, bump = require('gulp-bump')
	, fs = require('fs')
	, insert = require('gulp-insert')
;
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


function changelogMsg(version){
	var msg = '* REMEMBER TO UPDATE CHANGELOG ';
	if(version !== undefined){
		msg = msg + 'FOR v'+version;
	}
	msg = msg + ' *';
	console.log('*'.repeat(msg.length));
	console.log(msg);
	console.log('*'.repeat(msg.length));
}
function changelog(){
	var version = getPackageJsonVersion();
	changelogMsg(version);
	var date = new Date();
	return gulp.src('CHANGELOG.md')
		.pipe(insert.prepend('## v'+ version + ' - ' +date.toISOString().substring(0, 10)+'\n\n\n'))
		.pipe(gulp.dest('./'));
};

function bumpVersion(lvl){
	if(lvl !== 'prerelease'){
		changelogMsg();
	}
	return gulp.src(['package.json', 'package-lock.json'])
		.pipe(bump({type: lvl}))
		.pipe(gulp.dest('./'));
}

function getPackageJsonVersion () {
	return JSON.parse(fs.readFileSync('./package.json', 'utf8')).version;
};

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

gulp.task('bump:test', function(){
	return bumpVersion('prerelease');
});

gulp.task('bump:patch', function(){
	return bumpVersion('patch');
});

gulp.task('bump:minor', function(){
	return bumpVersion('minor');
});

gulp.task('bump:major', function(){
	return bumpVersion('major');
});

gulp.task('changelog', function(){
	return changelog();
});

