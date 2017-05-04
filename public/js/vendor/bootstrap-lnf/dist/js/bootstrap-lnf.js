(function($){
  $('body').on('click', '.openable .handler', function(e){
    $(this).closest('.openable').toggleClass('open');
  });
})(jQuery);
