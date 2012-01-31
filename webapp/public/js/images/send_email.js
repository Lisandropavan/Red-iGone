var sendemail = {
	init : function() {
		
		$('#close').click(function() {
			parent.$.fancybox.close();
			return false;
		})

	}
};

rig.ready('#send_email', function() {
	sendemail.init();
});
