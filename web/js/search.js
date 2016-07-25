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
	$('.fav').click(function() {
		var fav = $(this).attr('value');
		
		if ( $(this).hasClass('glyphicon-star-empty')){
			$.ajax({
			type: 'POST',
			url: bookmark,
			data: {fav: fav},
			dataType : 'json',
			});
			$(this).removeClass('glyphicon-star-empty').addClass('glyphicon-star');
		}
		else{
			$.ajax({
			type: 'POST',
			url: bookmark,
			data: {fav: fav},
			dataType : 'json',
			});
			$(this).removeClass('glyphicon-star');
			$(this).addClass('glyphicon-star-empty');
		}
	});
	$("#search").keyup(function() {
		var search = $(this).val();
		var lengthSearch = search.length;

		if(lengthSearch >=3){
			

			$.ajax({
				type: 'POST',
				url: autocompletion,
				data: {recherche: search},
				dataType : 'json',

				beforeSend: function() {
					$("#test option").remove();
					$("#test").attr('disabled', 'disabled');
				},

				success: function(data) {					
					console.log('Requete ok',data);
					$.each(data.nameTab, function(index,value) {
						$("#test").append($('<option>',{ value : value.id , text: value.name }));
						$("#test").removeAttr('disabled');
					});
					$.each(data.tagTab, function(index,value) {
						$("#test").append($('<option>',{ value : value.id , text: value.name }));
						$("#test").removeAttr('disabled');
					});
				},

				error: function() {
					console.log('Requete fail');					
				},
			});		
		}
	});
});