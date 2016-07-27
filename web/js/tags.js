$(document).ready(function(){
	var counttag= $('.counttag').attr('id');

	if(isdeleted == 0)
	{
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
	}
	else
	{
		isdeleted=1;
	}
	if( counttag<=2)
	{
		$("#addtagfield").removeAttr('disabled');
		$('#addtagbtn').click(function(){
			var content = $('#addtagfield').val();
			counttag++;
			$.ajax({
				type: 'POST',
				url: tagadd,
				data: {idfile: idfile, content: content},
				dataType : 'json',
				success: function(data){
					$("#taglist").append($('<p>',{ 'id': data.tabtag.idtag }));
					$("#"+data.tabtag.idtag).text(data.tabtag.name);
					$("#"+data.tabtag.idtag).append($('<span>',{'class': 'tagicon glyphicon glyphicon-remove', 'id': data.tabtag.idtag}));
					$("#"+data.tabtag.idtag).addClass('tag');
					$('.tagicon').click(function(){
						
						if(isdeleted==0)
						{
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
							isdeleted=1;
						}
						else
						{
							isdeleted=0;
							return;
						}
					});
				},

			});
		});
	}
	else{
		$("#addtagfield").attr('disabled', 'disabled');
	};
});