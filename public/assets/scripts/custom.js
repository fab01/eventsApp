$('.js-datepicker').datepicker({
  format: 'dd-mm-yyyy'
});

$('#confirm-delete').on('show.bs.modal', function(e) {
  $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
});

$('select#fm-14').on('change', function() {
  var $option = $("#fm-14").find("option:selected").val();
  if($option === '4' || $option === '5' || $option === '6') { // 4 and 5 are values for 1 single night.
    $('.one_night').show();
  } else {
    $('.one_night').hide();
  }
});

$(document).ready(function() {
  var $option = $("#fm-14").find("option:selected").val();
  if($option === '4' || $option === '5'  || $option === '6') { // 4 and 5 are values for 1 single night.
    $('.one_night').show();
  }
});
