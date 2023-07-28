import pkg from 'gulp';
const { src, dest, watch, series, parallel } = pkg;

import yargs from 'yargs';
// css related packages
import * as dartsass from 'sass'
import gulpsass from 'gulp-sass';
const sass = gulpsass( dartsass );
import cleanCss from 'gulp-clean-css';
import gulpif from 'gulp-if';
import postcss from 'gulp-postcss';
import sourcemaps from 'gulp-sourcemaps';
import autoprefixer from 'autoprefixer';
import del from 'del';
// image related packages
import imagemin from 'gulp-imagemin';
// script related packages
import concat from 'gulp-concat';
import minify from 'gulp-minify';
import rename from 'gulp-rename';
// constants
const PRODUCTION = yargs.argv.prod;

export const styles = () => {
  return src(['assets/src/scss/main.scss', 'assets/src/scss/admin.scss'])
    .pipe(gulpif(!PRODUCTION, sourcemaps.init()))
    .pipe(sass().on('error', sass.logError))
    .pipe(gulpif(PRODUCTION, postcss([ autoprefixer ])))
    .pipe(gulpif(PRODUCTION, cleanCss({compatibility:'*'})))
    .pipe(gulpif(!PRODUCTION, sourcemaps.write()))
    .pipe(dest((file) => {
			return 'assets/dist/css';
    }));
};

export const images = () => {
  return src(['assets/src/img/**/*.{jpg,jpeg,png,svg,gif,webp}', 'assets/src/vendor/img/**/*.{jpg,jpeg,png,svg,gif}'])
    .pipe(gulpif(PRODUCTION, imagemin()))
    .pipe(dest((file) => {
		if (file.dirname.includes('vendor')) {
			return 'assets/dist/vendor/img';
		}
		
		return 'assets/dist/img';
    }));
};

export const scripts = () => {
	return src(['assets/src/js/*.js'])
		.pipe(concat('temp.js')) // Concatenate all JS files into a temporary file
		.pipe(minify({
			ext: {
				min: '.js'
			},
			noSource: true
		}))
		.pipe(rename((path) => {
			if (path.basename.includes('admin')) {
				path.basename = 'admin.min';
			} else {
				path.basename = 'main.min';
			}
		}))
		.pipe(dest('assets/dist/js'));
  };

// continually run the compiling scripts while files are saved/changed/added
export const watchForChanges = () => {
  watch('assets/src/scss/**/*.scss', styles);
  watch('assets/src/js/**/*.js', scripts);
  watch('assets/src/img/**/*.{jpg,jpeg,png,svg,gif}', images);
};


////
// COMMANDS
////
// clean up the dist folder
export const clean = () => del(['assets/dist']);
// setup dev and build processes to reference in package.json
export const build = series(clean, parallel(styles, images, scripts));
export const dev = series(clean, build, watchForChanges);
// default command is dev if you just run `npm run`
export default dev;
