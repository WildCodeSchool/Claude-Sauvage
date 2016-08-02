$(document).ready(function(){
	$('.removeuserbtn').click(function(){
		var user = $(this).attr('id');
		console.log(user);
		$('#msgdelete').text("voulez-vous vraiment retirer l'user "+user+' du groupe ?');
		$('#userinput').val(user);
	});
});