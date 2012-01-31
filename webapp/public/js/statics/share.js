var share = {
	init : function() {
		$('.deli').mouseover(function() {
			$('.deli').toggleClass( 'delih' );
		});

		$('.deli').mouseout(function() {
			$('.deli').toggleClass( 'delih' );
		});

		$('.digg').mouseover(function() {
			$('.digg').toggleClass( 'diggh' );
		});

		$('.digg').mouseout(function() {
			$('.digg').toggleClass( 'diggh' );
		});

		$('.facebook').mouseover(function() {
			$('.facebook').toggleClass( 'facebookh' );
		});

		$('.facebook').mouseout(function() {
			$('.facebook').toggleClass( 'facebookh' );
		});

		$('.linkedin').mouseover(function() {
			$('.linkedin').toggleClass( 'linkedinh' );
		});

		$('.linkedin').mouseout(function() {
			$('.linkedin').toggleClass( 'linkedinh' );
		});

		$('.myspace').mouseover(function() {
			$('.myspace').toggleClass( 'myspaceh' );
		});

		$('.myspace').mouseout(function() {
			$('.myspace').toggleClass( 'myspaceh' );
		});	

		$('.reddit').mouseover(function() {
			$('.reddit').toggleClass( 'reddith' );
		});

		$('.reddit').mouseout(function() {
			$('.reddit').toggleClass( 'reddith' );
		});

		$('.stumble').mouseover(function() {
			$('.stumble').toggleClass( 'stumbleh' );
		});

		$('.stumble').mouseout(function() {
			$('.stumble').toggleClass( 'stumbleh' );
		});

		$('.twitter').mouseover(function() {
			$('.twitter').toggleClass( 'twitterh' );
		});

		$('.twitter').mouseout(function() {
			$('.twitter').toggleClass( 'twitterh' );
		});

		$('.google').mouseover(function() {
			$('.google').toggleClass( 'googleh' );
		});	

		$('.google').mouseout(function() {
			$('.google').toggleClass( 'googleh' );
		});
	}
};

rig.ready('#share_content', function() {
	share.init();
});