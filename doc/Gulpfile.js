var gulp = require('gulp'),
    shell = require('gulp-shell'),
    exec = require('child_process').exec;


gulp.task('default', function(){
  gulp.src('*.md', { read: false })
    .pipe(shell(
      ['pandoc --template=templates/oscar.html --standalone <%= file.path %> -o <%= f(file.path) %>'],
      {
        templateData: {
          f: function(s){
            return s.replace(/\.md/, '.html')
          }
        }
      }
    ))
});

gulp.task('watch', function(){
  gulp.watch('*.md', ['default']);
  gulp.watch('templates/oscar.html', ['default']);
})
