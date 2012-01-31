var select = {
	init : function() {
		$('#select_img').imgAreaSelect({keys: true, fadeSpeed: 200, handles: true, onSelectEnd: select.save_area }); 
		select.show_selection();
		select.remove_selection();
		select.clear_selection();
		//select.remove_all_selections();
		select.timeout();
	},

	timeout : function() {
		clearTimeout(timeoutId);
		timeoutId = setTimeout('location.href=\'/new-upload\'', 7200000);	
	},

	clear_selection : function() {
		$('#clear_selection').click(function() {
			$('#current_id').text('');			
			$('#select_img').imgAreaSelect({ hide: true });
			return false;
		});
	},

	all_selections : function() {
		$.get('/images/get_all_selections', function(data) {
			
			var link;
			$('#all_selections').text('');
			
			//if(data > 0) {
			//	$('#clear_selection').show();
			//	$('#remove_all_selections').show();
			//}
			
			if(data < 1) {
				$('#dotted_border').hide();
				$('#all_selections').hide();
			} else {
				$('#dotted_border').show();
				$('#all_selections').show();				
			}
						
			for (i=1; i<=data; i++) {
				link = '<span id="r'+i+'" class="remove pointer"><img class="img_bottom" src="/public/img/images/remove.png" /></span> <span id="'+i+'" class="red_eye_selection pointer">Selection '+i+'</span><br />';
				$('#all_selections').append(link);		
			}
			$('#current_id').text('');
			$('#select_img').imgAreaSelect({ hide: true });
			select.init();
			return false;
		});
	},

	show_selection : function () {
		$('.red_eye_selection').click(function() {
			var id = $(this).attr('id');
			$('#current_id').text(id);
			$.get('/images/get_selection', {'id': id }, function(data) {
				var coords = JSON.parse(data);
				$('#select_img').imgAreaSelect({x1: coords.x1, y1: coords.y1, x2: coords.x2, y2: coords.y2});
				return false; 
			});
		});
	},

	remove_selection : function() {
		$('.remove').click(function() {
			var id = $(this).attr('id');
			$.get('/images/remove_selection', {'id': id}, function(data) {
				var link;
				$('#all_selections').text('');
				
				/*if(data > 0 ) {
					$('#clear_selection').show();
					$('#remove_all_selections').show();
				} else {
					$('#clear_selection').hide();
					$('#remove_all_selections').hide();
				}*/
				if(data < 1) {
					$('#dotted_border').hide();
					$('#all_selections').hide();
				} else {
					$('#dotted_border').show();
					$('#all_selections').show();				
				}
												
				for (i=1; i<=data; i++) {
					link = '<span id="r'+i+'" class="remove pointer" href="#"><img class="img_bottom" src="/public/img/images/remove.png" /></span> <span id="'+i+'" class="red_eye_selection pointer">Selection '+i+'</span><br />';
					$('#all_selections').append(link);		
				}
				$('#current_id').text('');
				$('#select_img').imgAreaSelect({ hide: true });
				select.show_selection();
				select.remove_selection();	
				return false;
			});
		});
	},

	remove_all_selections : function() {
		$('#remove_all_selections').click(function() {
			$.get('/images/remove_all_selections', function(data) {
				if(data == '0') {
					//$('#remove_all_selections').hide();
					//$('#clear_selection').hide();
					$('#all_selections').text('');
										
					$('#current_id').text('');
					$('#select_img').imgAreaSelect({ hide: true });
				}
				return false;
			});
		});
	},

	save_area : function(img, selection) {
		var x1 = selection.x1;
		var y1 = selection.y1;
		var x2 = selection.x2;
		var y2 = selection.y2;
		if(!selection.width || !selection.height) {
			$('#current_id').text('');
			return false;
		} else {
			var id = $('#current_id').text();
			if(id == '') {
				id = 0;
			}
			
			$.get('/images/save_selection', {'id': id, 'x1': x1, 'y1': y1, 'x2': x2, 'y2': y2}, function(data) {
				var link;
				$('#all_selections').text('');
				
				/*if(data > 0 ) {
					$('#clear_selection').show();
					$('#remove_all_selections').show();
				} else {
					$('#clear_selection').hide();
					$('#remove_all_selections').hide();
				}*/
				if(data < 1) {
					$('#dotted_border').hide();
					$('#all_selections').hide();
				} else {
					$('#dotted_border').show();
					$('#all_selections').show();				
				}
				
				for (i=1; i<=data; i++) {
					link = '<span id="r'+i+'" class="remove pointer"><img class="img_bottom" src="/public/img/images/remove.png" /></span> <span id="'+i+'" class="red_eye_selection pointer">Selection '+i+'</span><br />';
					$('#all_selections').append(link);					
				}
				$('#current_id').text(i-1);
				select.show_selection();
				select.remove_selection();			
				return false;
			});
		}
	}
};

var timeoutId = 0;

rig.ready('#select', function() {
	$('#current_id').hide();
	select.all_selections();
});