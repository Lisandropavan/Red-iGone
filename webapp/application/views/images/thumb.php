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