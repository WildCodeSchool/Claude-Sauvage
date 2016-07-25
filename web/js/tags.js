
$('document').ready(function(){
	var counttag = $('.counttag').attr('id');
	console.log(counttag);
	if( counttag < 3 ){
		$("#addtagfield").removeAttr('disabled');
		$('#addtagbtn').click(function(){
			var content = $('#addtagfield').val();
			$.ajax({
				type: 'POST',
				url: tagadd,
				data: {idfile: idfile, content: content},
				dataType : 'json',
				success: function(data){
					console.log(data.tabtag.idtag);
					$("#taglist").append($('<p>',{ 'id': data.tabtag.idtag }));
					$("#"+data.tabtag.idtag).text(data.tabtag.name);
					$("#"+data.tabtag.idtag).append($('<span>',{'class': 'tagicon glyphicon glyphicon-remove', 'id': data.tabtag.idtag}));
					$("#"+data.tabtag.idtag).addClass('tag');
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
				}
			});
		});
	}
	else{
		$("#addtagfield").attr('disabled', 'disabled');
	};
	$('.tagicon').click(function(){
		var idtag = $(this).attr('id');
		$.ajax({
			type: 'POST',
			url: tagremove,
			data: {idtag: idtag},
			dataType : 'json',
		});
		$("#"+idtag).addClass("tagdisplay");
		$("#addtagfield").removeAttr('disabled');
		counttag --;
	});
});