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

	// suggestion tag.
	$("#addtagfield").keyup(function() {
		var tag = $(this).val();
		
		if((tag.length)>=3){
			var count = 0;
			$.ajax({
				type: 'POST',
				url: auto_tag,
				data: {recherche: tag},
				dataType : 'json',

				beforeSend: function() {
					console.log('Requete en cours');
					$("#tag_auto p").remove();
					$("#tag_auto h5").remove();
					$("#tag_auto").css("display", "none");
				},

				success: function(data) {					
					console.log('Requete ok',data);
					$("#tag_auto").css("display", "block");
					if (data.tagTab.length!=0){
						count = 0;
						$("#tag_auto").append($('<h5>',{ text: 'Mes Tags' }).addClass('bold'));
						$.each(data.tagTab, function(index,value) {
							if (count<3){
								$("#tag_auto").append($('<p>',{ text: value.name }));
								count ++;
							}
						});
					}
					if (data.grpTagTab.length!=0){
						count = 0;
						$("#tag_auto").append($('<h5>',{ text: 'Tags de mes groupes' }).addClass('bold'));
						$.each(data.grpTagTab, function(index,value) {
							if (count<3){
								$("#tag_auto").append($('<p>',{ text: value.name }));
								count ++;
							}
						});
					}
					$("#tag_auto p").click(function(){
						var remplace = $(this).text();
						$("#addtagfield").val(remplace);
					});
					if(data.tagTab.length==0 && data.grpTagTab.length==0){
						$("#tag_auto h5").remove();
						$("#tag_auto").append($('<h5>',{ text: 'Aucun résultat' }));
					}
				},

				error: function() {
					console.log('Requete fail');
				},
			});
		}
		else{
			$("#tag_auto p").remove();
			$("#tag_auto h5").remove();
			$("#tag_auto").css("display", "none");
		}
	});

	var tag_auto = $('#tag_auto');

	$('html').click(function(event){
		if(event.target.id == 'addtagfield') {
			tag_auto.fadeIn();
		}
		else{
			tag_auto.fadeOut();
		}
	});
});