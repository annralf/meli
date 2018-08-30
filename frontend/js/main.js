var host = window.location.hostname
var url = "../resources/front_manager.php";
var data = M.Chips.getInstance($('.chips').chips());
//var url = host+"/tokioExpress/resources/front_manager.php";
function initial(){
	//Start AWS counter
	$.post(url,{
		action:'get_aws_items',
		type : 'counter'
	}).done(function(e){
		var response = JSON.parse(e);
		var counter = response.amount+response.letter;
		$('.counter_aws_items').text(counter);
	});
	//Start AWS Prime counter
	$.post(url,{
		action:'get_aws_items_prime',
		type : 'counter'
	}).done(function(e){
		var response = JSON.parse(e);
		var counter = response.amount+response.letter;
			$('.counter_aws_items_prime').text(counter);
	});
	//Start AWS No Prime counter
	$.post(url,{
		action:'get_aws_items_noprime',
		type : 'counter'
	}).done(function(e){
		var response = JSON.parse(e);
		var counter = response.amount+response.letter;
			$('.counter_aws_items_no_prime').text(counter);
	});
	//Start MELI Created counter
	$.post(url,{
		action:'get_meli_items',
		type : 'counter'
	}).done(function(e){
		var response = JSON.parse(e);
		var counter = response.amount+response.letter;
			$('.counter_meli_items').text(counter);
	});
}

function get_aws_items(){
	$('#table_meli').hide();
	$('#table_aws > tbody > tr > td').remove();
	$.post(url,{
		action: 'get_aws_items',
		type: 'detail'
	}).done(function(e){
		var response = JSON.parse(e);
		$('#table_aws > tbody').append(response.result);
		$('#table_aws').show();
		$('#table_aws').DataTable();
	});
}

function get_aws_items_prime(){
	$('#table_meli').hide();
	$('#table_aws > tbody > tr > td').remove();
	$.post(url,{
		action: 'get_aws_items_prime',
		type: 'detail'
	}).done(function(e){
		var response = JSON.parse(e);
		$('#table_aws > tbody').append(response.result);
		$('#table_aws').show();
		$('#table_aws').DataTable();
	});
}

function get_aws_items_noprime(){
		$('#table_meli').hide();
	$('#table_aws > tbody > tr > td').remove();
	$.post(url,{
		action: 'get_aws_items_prime',
		type: 'detail'
	}).done(function(e){
		var response = JSON.parse(e);
		$('#table_aws > tbody').append(response.result);
		$('#table_aws').show();
		$('#table_aws').DataTable();
	});
}

function get_meli_items(){
	$('#table_meli > tbody > tr > td').remove();
	$('#table_aws').hide();
	$.post(url,{
		action: 'get_meli_items',
		type: 'detail'
	}).done(function(e){
		var response = JSON.parse(e);
		$('#table_meli > tbody').append(response.result);
		$('#table_meli').show();
		$('#table_meli').DataTable();
	});
}

function get_usd_price(){
	$.post(url,{
		action:'get_usd_price'
	}).done(function(e){
		var result = JSON.parse(e);
		$('#dollar_price').val(result.result);
	});
}

function set_usd_price(){
	$.post(url,{
		action:'update_usd_price',
		dollar_price : $('#dollar_price').val()
	}).done(function(e){
		var result = JSON.parse(e);
		if (result.result == 1) {
			alert('Actualizado con éxito!');
		}else{
			alert('Ha ocurrido un problema con la actualización!');			
		}
	});
}

function get_revenue(){
	$.post(url,{
		action:'get_revenue'
	}).done(function(e){
		var result = JSON.parse(e);
		$('#revenue').val(result.result);
	});
}

function set_revenue(){
	$.post(url,{
		action:'update_revenue',
		revenue : $('#revenue').val()
	}).done(function(e){
		var result = JSON.parse(e);
		if (result.result == 1) {
			alert('Actualizado con éxito!');
		}else{
			alert('Ha ocurrido un problema con la actualización!');			
		}
	});
}

function get_description(){
	$.post(url,{
		action : 'get_description'
	}).done(function(e){
		var r = JSON.parse(e);
		$("#product_description_dt").val(r.product_description_dt);
		$("#product_description_ai").val(r.product_description_ai);
		$("#product_description_dd").val(r.product_description_dd);
		$("#product_description_rp").val(r.product_description_rp);
	});
}

function set_description(){
	$.post(url,{
		action : 'update_description',
		product_description_dt: $('#product_description_dt').val(),
		product_description_ai: $('#product_description_ai').val(),
		product_description_dd: $('#product_description_dd').val(),
		product_description_rp: $('#product_description_rp').val()
	}).done(function(e){
		var result = JSON.parse(e);
		if (result.result == 1) {
			alert('Actualizado con éxito!');
		}else{
			alert('Ha ocurrido un problema con la actualización!');			
		}
	});
}

function get_warranty(){
	$.post(url,{
		action:'get_warranty'
	}).done(function(e){
		var result = JSON.parse(e);
		$('#product_warrant').text(result.result);
	});
}

function set_warranty(){
	$.post(url,{
		action:'update_warranty',
		product_warrant : $('#product_warrant').val()
	}).done(function(e){
		var result = JSON.parse(e);
		if (result.result == 1) {
			alert('Actualizado con éxito!');
		}else{
			alert('Ha ocurrido un problema con la actualización!');			
		}
	});
}

function get_message(){
	$.post(url,{
		action:'get_message'
	}).done(function(e){
		var r = JSON.parse(e);
		$('#message_body').text(r.message_body);
		$('#message_subject').text(r.message_subject);
		$('#message_subject_position').text(r.message_subject_position);
	});
}

function set_message(){
	$.post(url,{
		action:'update_message',
		message_body : $('#message_body').val(),
		message_subject : $('#message_subject').val(),
		message_subject_position : $('#message_subject_position').val(),
	}).done(function(e){
		var result = JSON.parse(e);
		if (result.result == 1) {
			alert('Actualizado con éxito!');
		}else{
			alert('Ha ocurrido un problema con la actualización!');			
		}
	})
}

function get_key_words(){
	$.post(url,{
		action: 'get_key_words'
	}).done(function(e){
		var result = JSON.parse(e);
		var key_words = result.key_words;
		for(var i in key_words){
			data.addChip({tag:key_words[i]});
		}
	});
}

function set_key_words(){
	var list = data.chipsData;
	console.log(list);
	for(var i in list){
				console.log(list[i]);				
			}
}
