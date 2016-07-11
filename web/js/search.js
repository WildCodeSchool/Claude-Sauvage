// $(document).ready(function(){
// 	$("#search").keyup(function(){
// 		recherche = $(this).val();

// 		$.ajax({
// 			type: "post",
// 			url: "{{ path('ged_search') }}",
// 			dataType: "json",
// 			data: {recherche : recherche},
// 			success : function(response) {
//             	document.getElementById("sresult").innerHTML = "";
//             	if(response.length === 1){
//                		var elmt = document.getElementById("sresult");
//                 	elmt.style.display = "block";

// 					var result = response[0];
//                 	document.getElementById("sresult").innerHTML = "<div class=resultat><p>"+result.name"</p></div>";

//             	}
//            		else {
//               		for(var i =0;i <= response.length-1;i++) {
//                 		var elmt = document.getElementById("sresult");
//                			elmt.style.display = "block";

//                 		var result = response[i];
//                 		document.getElementById("sresult").innerHTML += "<div class=resultat><p>"+result.name"</p></div>";
//               		}
//             	}
//           	}
//         });
//     });
});