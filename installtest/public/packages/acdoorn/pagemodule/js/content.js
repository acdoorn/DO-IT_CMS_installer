$(document).ready(function() {
	$( "#sectiontabs" ).tabs();
});

$('#choose').change(function(event) {
	var alltabs = $(".update");
	var y = 0;
	for ( var x = 0; x < alltabs.length; x++ )
	{
		if($(alltabs[x]).is(":visible")) {
			$(alltabs[x]).html($('#choose').val());
			// console.log($(alltabs[x]).children('form').children('input'));
			$(alltabs[x]).children('.sections').each(function() {
				var name = $(this).attr('name');
				$(this).attr('name', 'section'+(x+1));
			});
			$(alltabs[x]).children('.articlelist').each(function() {
				$(this).attr('name', $(this).attr('name')+(x+1));
			});
			$(alltabs[x]).children('.input-group').children('input').each(function() {
				$(this).attr('name', $(this).attr('name')+(x+1));
			});
			$(alltabs[x]).children('.input-group').children('textarea').each(function() {
				$(this).attr('name', $(this).attr('name')+(x+1));
			});
			$(alltabs[x]).children('.input-group').children('.contenttemplates').each(function() {
				var name = $(this).attr('name');
				$(this).attr('name', 'contenttemplates'+(x+1));
			});
		}
	}
}); 


function refreshchoose() {
//refreshes per section because it doesn't automatically get reassigned to the current visible section
	$("#choose").load(location.href + " #choose > *");
}
