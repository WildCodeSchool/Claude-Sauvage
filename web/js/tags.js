$('document').ready(function(){
	$('.tagicon').click(function(){
		var idtag = $(this).attr('id');
		$.ajax({
			type: 'POST',
			url: tagremove,
			data: {idtag: idtag},
			dataType : 'json',
		});
		$("#"+idtag).addClass("tagdisplay");
		console.log('ca marche'+idtag);
	});

	$('#addtagbtn').click(function(){
		var content = $('#addtagfield').val();
		$.ajax({
			type: 'POST',
			url: tagadd,
			data: {idfile: idfile, content: content},
			dataType : 'json',
		});
	});

});