$(document).ready(function(){
	console.log($('#file-img').width());
	console.log($('#file-part').width());
	if ((($('#file-part').width()-$('#file-img').width())%2==1))
	{
		console.log('reel');
		var margin = (($('#file-part').width()-$('#file-img').width())/2)-8.5;
	}
	else
	{
		console.log('entier');
		var margin = (($('#file-part').width()-$('#file-img').width())/2)-8;
	}
	console.log(margin);
	$('#file-img').css('margin-left',margin);
	$('#file-img').css('margin-right',margin);

	$('#btn-com').click(function(){
			$('#com-part').toggleClass('col-md-3');
			$('#com-part').toggleClass('showform');
			$('#form').toggleClass('showform');
			$('#file-part').toggleClass('col-md-9');
			$('#file-part').toggleClass('col-md-12');
			if ((($('#file-part').width()-$('#file-img').width())%2==1))
			{
				console.log('reel');
				var margin = (($('#file-part').width()-$('#file-img').width())/2)-8.5;
			}
			else
			{
				console.log('entier');
				var margin = (($('#file-part').width()-$('#file-img').width())/2)-8;
			}
			console.log(margin);
			$('#file-img').css('margin-left',margin);
			$('#file-img').css('margin-right',margin);
		});
		$('#modal-partage').on('shown.bs.modal', function () {
  		$('#modal-partage').focus()
		})
});