$('document').ready(function(){
	$('.tag').click(function(){
		var idtag = $(this).attr('id');
		$.ajax({
			type: 'POST',
			url: tagremove,
			data: {idtag: idtag},
			dataType : 'json',
		});
		$(this).addClass("tagdisplay");
		console.log('ca marche'+idtag);
	});
});