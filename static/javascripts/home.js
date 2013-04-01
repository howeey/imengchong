$(document).ready(function() {
  var $main;
  $main = $('#main');
  $main.imagesLoaded(function(){
    $main.masonry({
      columnWidth: 239,
      itemSelector: '.mc_grid',
      animate: true,
      animationOptions: {
        duration: 500
      }
    });
  }, function(){
    // Empty Callback
    return true;
  });
  return true;
});