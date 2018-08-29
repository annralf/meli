var host = window.location.hostname
var url = "../resources/front_manager.php";
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