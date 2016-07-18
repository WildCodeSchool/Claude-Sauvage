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
});