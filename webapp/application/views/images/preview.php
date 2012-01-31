<div id="top"></div>

  <div id="main_content">
    <div id ="preview">
    	<div id="step3">
    	Step 3 - Download your enhanced image
    	</div>
    	<div id="outer">
    		<br /><br />
    		<div class="polaroid_frame" style="width: <?php echo 135+(2*$frame_width) ?>px;">
    			<div class="top_left">
    			</div>
    			<div class="top_center" style="width: <?=$frame_width ?>px;">
    			</div>
    			<div class="top_right">
    			</div>

    			<div class="space"></div>

    			<div class="top_left">
    			</div>
    			<div class="top_center" style="width: <?=$frame_width ?>px;">
    			</div>
    			<div class="top_right">
    			</div>

    			<div class="clearboth"></div>

    			<div class="middle_left" style="height: <?=$frame_height ?>px;">
    			</div>
    			<div class="middle_center" style="width: <?=$frame_width ?>px;">
    				<?='<img class="preview_img" src="'.$original_thumb['path'].$original_thumb['name'].'" /> ' ?>
    			</div>
    			<div class="middle_right" style="height: <?=$frame_height ?>px;">
    			</div>

    			<div class="space"></div>
			
    			<div class="middle_left" style="height: <?=$frame_height ?>px;">
    			</div>
    			<div class="middle_center" style="width: <?=$frame_width ?>px; height: <?=$frame_height ?>px;">
    				<div class="middle_center_inside" style="width: <?=$frame_width ?>px; height: <?=$frame_height ?>px;">
    				<?
    					if(isset($current_thumb)) {
    						echo '<img class="preview_img" id="current" src="'.WEBAPP_DOWNLOAD_URL.$current_thumb.'" />';
    					} else {
    						echo '<img class="preview_img" id="current" src="/public/img/common/loading_big.gif" />';
    					}
    				 ?>
    				</div>
    			</div>
    			<div class="middle_right" style="height: <?=$frame_height ?>px;">
    			</div>

    			<div class="clearboth"></div>

    			<div class="bottom_left">
    			</div>
    			<div class="bottom_center" style="width: <?=$frame_width ?>px;">
    				<br />before
    			</div>
    			<div class="bottom_right">
    			</div>

    			<div class="space"></div>

    			<div class="bottom_left">
    			</div>
    			<div class="bottom_center" style="width: <?=$frame_width ?>px;">
    				<br />after
    			</div>
    			<div class="bottom_right">
    			</div>

    			<div class="clearboth"></div>
    			<div class="divider"></div>
    			<div class="clearboth"></div>
		
    			<div class="menu_box">
    				<div class="menu_header" style="width: <?=$frame_width+97 ?>px;"><div id="box_finetune">
    					<img id="icon_finetune" class="menu_icon" src="/public/img/images/finetune.png" /></div><div id="text_finetune">Fine tune</div>
    				</div>
			
    				<div class="menu_header2" style="width: <?=$frame_width+19 ?>px;"><div id="box_done">
    					<img id="icon_done" class="menu_icon" src="/public/img/images/done.png" /></div><div id="text_done">I'm done</div>
    					</div>
    			</div>

    			<div class="clearboth"></div>

    			<div class="menu_box">
    				<div class="menu_item pointer more" style="width: <?=$frame_width+97 ?>px;"><div id="box_more">
    				<img id="more" class="menu_icon" src="/public/img/images/more.png" /></div><div id="text_more">Remove more red</div>
    				</div>
			
    				<div class="menu_item2 pointer" style="width: <?=$frame_width+19 ?>px;"><div id="box_download">
    					<!-- REMOVE target blank when switching Download Image to Save as...-->
    					<?php
    						if(isset($current_image) && !empty($current_image)) {
    							echo '<a id="download" target="_blank" href="'.WEBAPP_DOWNLOAD_URL.$current_image.'">';
    							//echo '<a id="download" href="/download-image/'.$current_image.'">';
    						} else {
    							echo '<a id="download" href="#">';
    						}
    					?>
    					<img id="icon_download" class="menu_icon" src="/public/img/images/download_final.png" /></a></div>
    					<div id="text_download"><?php
    						if(isset($current_image) && !empty($current_image)) {
    							echo '<a id="download2" target="_blank" href="'.WEBAPP_DOWNLOAD_URL.$current_image.'">';
    							//echo '<a id="download" href="/download-image/'.$current_image.'">';
    						} else {
    							echo '<a id="download2" href="#">';
    						}
    					?>Download</a></div>
    					</div>				
    			</div>

    			<div class="clearboth"></div>

    			<div class="menu_box">
    				<div class="menu_item pointer less" style="width: <?=$frame_width+97 ?>px;"><div id="box_less">
    					<img id="less" class="menu_icon" src="/public/img/images/less.png" /></div><div id="text_less">Remove less red</div></div>

    				<div class="menu_item2 pointer" style="width: <?=$frame_width+19 ?>px;"><div id="box_email">
    					<img id="icon_email" class="menu_icon" src="/public/img/images/email.png" /></div><div id="text_email"><a id="email_image" href="/email-image<?php $img = isset($current_image) ? '?image='.$current_image : ''; echo $img; ?>">Email</a></div></div>
    			</div>
			
    			<div class="menu_box">
    				<div class="menu_item pointer empty" style="width: <?=$frame_width+97 ?>px;">&nbsp;</div>
				
    				<div class="menu_item2 pointer" style="width: <?=$frame_width+19 ?>px;"><div id="box_start_over">
    					<img id="icon_start_over" class="menu_icon" src="/public/img/images/start_over.png" /></div><a href="/new-upload"><div id="text_start_over">Start over</div></a></div>
    			</div>			
    		</div>
    		<div class="clearboth"></div>
    		<div id="thumbs">
    			<?php
    				if(isset($thumbs)) {					
    					if(is_array($thumbs)) {

    						echo '<div id="all_thumbs">';
    							echo '<span class="larger">Your Gallery</span><br /><span class="medium">(click to select)</span>';
    						echo '</div>';

    						echo '<div class="space"></div>';	
    						echo '<div class="clearboth"></div>';

    						foreach($thumbs as $threshold=>$t) {
    							$name = substr($t['name'],6,(strlen(($t['name']))-6));
    							echo '<img class="mini_thumb" id="'.$threshold.'" name="'.$name. '" src="'.$t['path'].$t['name'].'" width="100px"> ';
    						}
    					} else {
    						echo "<!-- Could not load thumbnails. //-->";
    					}
    			}
    			?>
    		</div>
    		<div id="current_queue_id"><?=$queue?></div>
    		<div id="threshold"><?=$th; ?></div>
    		<div class="clearboth"></div>
    	</div>
  	</div>
  </div>

<div id="bottom">
  <br />
	<script type="text/javascript"><!--
	google_ad_client = "pub-9625394623400758";
	/* RiG - Leaderboard */
	google_ad_slot = "9716597445";
	google_ad_width = 728;
	google_ad_height = 90;
	//-->
	</script>
	<script type="text/javascript"
	src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
	</script>
	<br />
</div>