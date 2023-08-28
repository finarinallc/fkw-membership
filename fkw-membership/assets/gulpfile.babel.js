import pkg from 'gulp';
const { src, dest, watch, series, parallel } = pkg;

import yargs from 'yargs';
// css related packages
import * as dartsass from 'sass'
import gulpsass from 'gulp-sass';
const sass = gulpsass(dartsass);
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
  return src(['src/scss/main.scss', 'src/scss/admin.scss'])
    .pipe(gulpif(!PRODUCTION, sourcemaps.init()))
    .pipe(sass().on('error', sass.logError))
    .pipe(gulpif(PRODUCTION, postcss([autoprefixer])))
    .pipe(gulpif(PRODUCTION, cleanCss({ compatibility: '*' })))
    .pipe(gulpif(!PRODUCTION, sourcemaps.write()))
    .pipe(dest((file) => {
      return 'dist/css';
    }));
};

export const images = () => {
  return src(['src/img/**/*.{jpg,jpeg,png,svg,gif,webp}', 'src/vendor/img/**/*.{jpg,jpeg,png,svg,gif}'])
    .pipe(gulpif(PRODUCTION, imagemin()))
    .pipe(dest((file) => {
      if (file.dirname.includes('vendor')) {
        return 'dist/vendor/img';
      }

      return 'dist/img';
    }));
};

export const scripts = () => {
  return src(['src/js/*.js'])
    .pipe(minify({
      ext: {
        min: '.min.js'
      },
      noSource: true
    }))
    .pipe(dest('dist/js'));
};

// continually run the compiling scripts while files are saved/changed/added
export const watchForChanges = () => {
  watch('src/scss/**/*.scss', styles);
  watch('src/js/**/*.js', scripts);
  watch('src/img/**/*.{jpg,jpeg,png,svg,gif}', images);
};


////
// COMMANDS
////
// clean up the dist folder
export const clean = () => del(['dist']);
// setup dev and build processes to reference in package.json
export const build = series(clean, parallel(styles, images, scripts));
export const dev = series(clean, build, watchForChanges);
// default command is dev if you just run `npm run`
export default dev;
