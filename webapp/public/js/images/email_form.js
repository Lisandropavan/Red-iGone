var emailform = {
	init : function() {
		
		$('#close').click(function() {
			parent.$.fancybox.close();
			return false;
		})
	
		/* Disabled because not working in Chrome/Safari
		probably disabled before submit can happen */
		/*
		$('#submit_email').click(function(){
			$('#submit_email').attr('disabled', 'disabled');
		});
    */
	}
};

rig.ready('#email_form', function() {
	emailform.init();
});