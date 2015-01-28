$('#choose').change(function(event) {
	var editmenudiv = $("#editmenudiv");
	var y = 0;

	$(editmenudiv).html($('#choose').val());
    $('.dd').nestable({}).on('change', function() {
        var newOrder = $('.dd').nestable('serialize');
        newOrder = JSON.stringify(newOrder);
        console.log('New order: ', newOrder);
        $('#neworder').val(newOrder);
        $('.dd-list').children('br').remove();
        $('.dd-list').children('p').remove();
      });
    $("ul, li").disableSelection();
}); 

$('div#link').click(function() {
    $(this).siblings('div#link').each(function() {
        var closemenu = $(this).children('ol');
        if (closemenu.is(":visible")) {
            closemenu.fadeOut();
        }
    });
    var submenu = $(this).children('ol');
    if (submenu.is(":visible")) {
        submenu.fadeOut();
    } else {
        submenu.fadeIn();
    }
});
var submenu_active = false;
 
$('div#link').mouseenter(function() {
    submenu_active = true;
});
 
$('div#link').mouseleave(function() {
    submenu_active = false;
    setTimeout(function() { if (submenu_active === false) $('ol#submenu').fadeOut(); }, 400);
});

$('span#menuhelp').mouseenter(function() {
    $(this).children('div').fadeIn();
});

$('span#menuhelp').mouseleave(function() {
    $(this).children('div').fadeOut()
});

// $('#menuitemname').on('input propertychange paste', function(event) {
//     var newname = $("#changename").val();
//     alert(newname);
//     $('#editable').text(newname);
// }); 

// $('#menuitemname').change(function(event) {
//     var newname = $("#changename").val();
//     alert(newname);
//     $('#editable').text(newname);
// }); 

$(document).on("keyup",'#changename' ,function() {
    var newname = $(this).val();
    $('#editable').text(newname);
});

