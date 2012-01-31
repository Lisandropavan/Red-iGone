var rig = {
	ready : function(id, callback) {
		return $(document).ready(function() {
		  $(id).each(function(i, ea) {
			try {
			  (callback || function(){}).apply(ea);
			} catch(e) {}
		  });
		});
	}
};