$(document).ready(function(){

	jQuery.ajaxSetup( {xhr:function(){
		//return new window.XMLHttpRequest();
		try{
			if(window.ActiveXObject)
			return new window.ActiveXObject("Microsoft.XMLHTTP");
		} catch(e){}
		return new window.XMLHttpRequest();
	 	}  
	});
	
	$('.contact').mouseover(function() {
		$('.contact').toggleClass( 'contacth' );
	});
	
	$('.contact').mouseout(function() {
		$('.contact').toggleClass( 'contacth' );
	});

	$('.share').mouseover(function() {
		$('.share').toggleClass( 'shareh' );
	});
	
	$('.share').mouseout(function() {
		$('.share').toggleClass( 'shareh' );
	});
	
	$('.home').mouseover(function() {
		$('.home').toggleClass( 'homeh' );
	});
	
	$('.home').mouseout(function() {
		$('.home').toggleClass( 'homeh' );
	});

	$('.help').mouseover(function() {
		$('.help').toggleClass( 'helph' );
	});
	
	$('.help').mouseout(function() {
		$('.help').toggleClass( 'helph' );
	});

	$('.about').mouseover(function() {
		$('.about').toggleClass( 'abouth' );
	});
	
	$('.about').mouseout(function() {
		$('.about').toggleClass( 'abouth' );
	});	
});