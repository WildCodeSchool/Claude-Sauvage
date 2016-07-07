$(document).ready(function(){
	$("#recherche").keyup(function(){
		var recherche = $(this).val();
		var data = 'motclef=' + recherche;
		if (recherche.length>2) {
			$.ajax({
				type: "GET",
				url:"result.php",
				data: data,
			
				success: function(server_responce){
					$('#resultat').html(server_responce).show();
				}
			});
		}
	});
});
