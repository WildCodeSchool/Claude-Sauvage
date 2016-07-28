$(document).ready(function(){
	$('#useraddbtn').click(function(){
		var useradd = $('#useraddfield').val();
		$.ajax({
			type: 'POST',
			url: useraddpath,
			data: {useradd: useradd, idgroup: idgroup},
			dataType : 'json',
			success: function(data){
				console.log(data);
				$('#memberslist').append($('<p>', {'text': data.newuser.username, 'id': data.newuser.id } ));
				$("#"+data.newuser.id).append($('<span>',{'class': 'glyphicon glyphicon-remove removeuserbtn', 'id': data.newuser.username }));
				$("#"+data.newuser.username).attr('data-toggle','modal');
				$("#"+data.newuser.username).attr('data-target','#modal-removeuser');
				
				$('.removeuserbtn').click(function(){
					var username = $(this).attr('id');
					console.log(username);
					$('#msgdelete').text('voulez-vous vraiment retirer '+username+' du groupe ?');
					$('#usernameinput').val(username);
					$('#idgroupinput').val(idgroup);
				});
			},
		});
	});

	$('.removeuserbtn').click(function(){
		var username = $(this).attr('id');
		console.log(username);
		$('#msgdelete').text('voulez-vous vraiment retirer '+username+' du groupe ?');
		$('#usernameinput').val(username);
		$('#idgroupinput').val(idgroup);
	});
});