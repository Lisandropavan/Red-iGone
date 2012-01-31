var preview = {
	init : function() {
		preview.less();
		preview.more();
		preview.show();
		preview.timeout();
		preview.imageStatus();
	},
	
	timeout : function() {
		clearTimeout(timeoutId);
		timeoutId = setTimeout('location.href=\'/new-upload\'', 7200000);	
	},
	
	imageStatus : function() {
		var id = $('#current_queue_id').text();
		if(id != '') {
			$.get('/images/get_status', { 'id': id }, function(r) {
					if(r == 'new' || r =='processing') {
						setTimeout('preview.imageStatus()', 2000);
					} else if(r != 'error') {
						$('#current').attr('src', '/image-download?img=thumb_'+r);
						$('#download').attr('href', '/image-download?img='+r);
						$('#download').attr('target', '_blank');
						$('#download2').attr('href', '/image-download?img='+r);
						$('#download2').attr('target', '_blank');
						$('#email_image').attr('href', '/email-image?image='+r);
						//$('#less').attr('src', '/public/img/images/less.png');					
						//$('#more').attr('src', '/public/img/images/more.png');
						$('#thumbs').load('/images/thumb', function() {
						$('#current_queue_id').text('');
						preview.show();
						return false;
						});
					} else if( r == 'error') {
						//print error message
					}
			 })
		}
	},

	show : function() {
		$('.mini_thumb').click(function() {
				var options = { to: '#current', className: 'ui-effects-transfer' };
				$(this).effect('transfer', options, 400, preview.changeImage);
				return false;
		})
	},

	changeImage : function() {
		var src = $(this).attr('src');
		var th = $(this).attr('id');
		var img = $(this).attr('name');
		$('#current').attr('src', src);
		$('#threshold').text(th);
		//$('#download').attr('href', '/download-image/'+img);
		$('#download').attr('href', '/image-download?img='+img);
		$('#download').attr('target', '_blank');
		$('#download2').attr('href', '/image-download?img='+img);
		$('#download2').attr('target', '_blank');
		$('#email_image').attr('href', '/email-image?image='+img);		
		$.post("/images/preview", {"th": th});
		return false;		
	},

	less : function() {
		$('.less').click(function() {

			var limit = false;		
			var th = parseInt($('#threshold').text()) - 25;
			if(th < 0) {
				th = 0;
				limit = true;
			} else if(th > 100) {
				th = 100;
				limit = true;
			}		

			$.get('/images/generate_thumb', { 'th': th }, function(r) {
				var data = eval(r);
				if(data.status == 'done') {
					$('#current').attr('src', data.path+data.thumb);
					$('#download').attr('href', data.path+data.name);
					$('#download2').attr('href', data.path+data.name);
					$('#email_image').attr('href', '/email-image?image='+data.path+data.name);					
					$('#threshold').text(th);
					if(!limit) {
						var options = { to: '#current', className: 'ui-effects-transfer' };
						$('.less').effect('transfer', options, 400);
					}
					$('#thumbs').load('/images/thumb', function() {						
						preview.show();
						waiting = false;
						return false;
					});
				} else if(data.status == 'queued') {
					$('#current_queue_id').text(data.queue_id);
					$('#threshold').text(th);
					$('#current').attr('src', '/public/img/common/loading_big.gif');
					preview.imageStatus();
					return false;
				} else if(data.status == 'busy') {
					return false;
				}	else if(data.status == 'error') {
					return false;
				}
				return false;
			});
		})
	},

	more : function() {
		$('.more').click(function() {
		
			var limit = false;
			var th = parseInt($('#threshold').text()) + 25;
			if(th < 0) {
				th = 0;
				limit = true;
			} else if(th > 100) {
				th = 100;
				limit = true;
			}

			$.get('/images/generate_thumb', { 'th': th }, function(r) {
				var data = eval(r);
				if(data.status == 'done') {
					$('#current').attr('src', data.path+data.thumb);
					$('#download').attr('href', data.path+data.name);
					$('#download2').attr('href', data.path+data.name);
					$('#email_image').attr('href', '/email-image?image='+data.path+data.name);						
					$('#threshold').text(th);
					if(!limit) {
						var options = { to: '#current', className: 'ui-effects-transfer' };
						$('.more').effect('transfer', options, 400);
					}		
					$('#thumbs').load('/images/thumb', function() {
						preview.show();
						return false;
					});
				} else if(data.status == 'queued') {
					$('#current_queue_id').text(data.queue_id);
					$('#threshold').text(th);
					$('#current').attr('src', '/public/img/common/loading_big.gif');
					preview.imageStatus();
					return false;
				}	else if(data.status == 'busy') {
					return false;
				} else if(data.status == 'error') {
					return false;
				}
				return false;
			});
		})
	}	
	
};

var timeoutId = 0;

rig.ready('#preview', function() {
	
	$("#email_image").fancybox({
		'width'				: 800,
		'height'			: 280,
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe'
	});
	
	preview.init();	
});