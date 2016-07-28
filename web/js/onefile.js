$(document).ready(function(){
	console.log($('#file-img').width());
	if($('#file-img').width() < 500)
	{

		$('#file-img').css('margin-left','35%');
		$('#file-img').css('width','30%');
	}
	else
	{
		$('#file-img').css('margin-left','5%');
		$('#file-img').css('max-width','90%');
	}
});