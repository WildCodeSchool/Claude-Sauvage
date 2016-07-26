$(document).ready(function(){
	$('#addcomment').click(function(){
		var content = $('#comcontent').val();
		$.ajax({
			type: 'POST',
			url: newcomment,
			data: {content: content, idfile : idfile},
			dataType : 'json',
			success: function(data){
				var date = new Date();
				var year = date.getYear()-100;
				if (date.getMinutes()<10)
				{
					var minutes='0'+date.getMinutes();
				}
				else
				{
					var minutes=date.getMinutes();	
				}
				if(date.getMonth()<10)
				{
					var month='0'+(date.getMonth()+1);
				}
				else
				{
					var month=date.getMonth()+1;
				}
				var showDate = date.getDate()+"/"+(month)+ "/" +20+year+' '+date.getHours()+':'+minutes+':'+date.getSeconds(); 
				$('.existing-com').append($('<div>',{ 'id': data.comtab['0'].idcom }));
				$('#'+data.comtab['0'].idcom ).addClass('one-comment')
				$('#'+data.comtab['0'].idcom ).append($('<p>',{'text': data.comtab['0'].owner+' '+showDate }))
				$('#'+data.comtab['0'].idcom ).append($('<p>',{'text': data.comtab['0'].content }))
			},
		});

	});

});