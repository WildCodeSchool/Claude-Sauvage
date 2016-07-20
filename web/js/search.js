$("document").ready(function() { 

    $("#categories").change(function() {
		var categorie = $(this).val();
		if ( categorie != 0 ) {
			$.ajax({
			type: 'POST',
			url: path,
			data: {categorie: categorie},
			dataType : 'json',
				beforeSend: function() {
					console.log('On charge');
					$("#sscategories option").remove();
					$("#sscategories").append($('<option>',{ value:0, text: "Aucune sous-catégorie"}));
					$("#sscategories").attr('disabled', 'disabled');
				},
				success: function(data) {
				console.log('Requete ok',data);
					$("#sscategories option").remove();
					$("#sscategories").append($('<option>',{ value:0, text: "Toutes les sous-catégories" }));
					$.each(data.ssCategorieTab, function(index,value) {
						$("#sscategories").append($('<option>',{ value : value.id , text: value.name }));
						$("#sscategories").removeAttr('disabled');
					});
				},
			});
		}
		else {
			$("#sscategories option").remove();
			$("#sscategories").append($('<option>',{ value:0, text: "Toutes les sous-catégories" }));
			$("#sscategories").attr('disabled', 'disabled');
		}
    });
	$('#fav').click(function() {
		var fav = $(this).attr('value');
		if ( $('#fav').is('.glyphicon-star-empty')){
			$.ajax({
			type: 'POST',
			url: bookmark,
			data: {fav: fav},
			dataType : 'json',
				beforeSend: function() {
					console.log('On charge');
					document.getElementById("fav").className = "glyphicon glyphicon-star";
				},
				success: function() {
				console.log('Requete ok');
					document.getElementById("fav").className = "glyphicon glyphicon-star";
				},
				error: function() {
				    alert( "Une erreur est survenue !" )
				 }
			});
		}
		else{
			var fav = $(this).attr('value');
			$.ajax({
			type: 'POST',
			url: bookmark,
			data: {fav: fav},
			dataType : 'json',
				beforeSend: function() {
					console.log('On charge');
					document.getElementById("fav").className = "glyphicon glyphicon-star-empty";
				},
				success: function() {
				console.log('Requete ok');
					document.getElementById("fav").className = "glyphicon glyphicon-star-empty";
				}
			});
		}
	});
});