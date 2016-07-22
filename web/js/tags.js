$('document').ready(function(){
	$('.tag').click(function(){
		var idtag = $(this).attr('id');
		var tagremove = "{{ path('ged_removetag', {'id': "+idtag+" } ) }}";
		$.ajax({
		type: 'POST',
		url: tagremove,
		data: {id: idtag},
		dataType : 'json',
			success: function() {
				console.log('Requete ok');
				$(this).className = "tagdisplay";
				alert('ca marche'+idtag);
			},
			error: function() {
			    alert( "TAMER L'ERREUR  "+tagremove)
			 }
		});
	});
});