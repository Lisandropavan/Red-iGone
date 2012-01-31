<div id="email_form">
  <?php
  	if(isset($error)) {
  		echo "<br /><br />You have to wait for your image to be created before sending an email.";
  		echo '<br /><br />';
  		echo '<a id="close" href="#">Close</a>';		
  	} else {
  ?>

  <div class="left form">
    <form id="image_email" name="image_email" action="/send-email" method="post">
  	<div class="row">
  	<span class="label">To:</span>
  	<span	class="formw"><input type="text" name="to_email" size="30" /></span>
  	</div>

  	<div class="row">
  	<span class="label">Subject:</span>
  	<span	class="formw"><input type="text" name="email_subject" size="30" /></span>
  	</div>

  	<div class="row">
  	<span class="label">Message:</span>
  	<span class="formw"><textarea name="email_message" cols="26" rows="7"></textarea></span>
  	</div>

  	<div class="row">
  	<span class="label"></span>
  	<span class="formw"><input id="submit_email" type="submit" value="Send Email" /></span>
  	</div>

    <div class="clearboth">
    &nbsp;
    </div>
  	<input type="hidden" name="filename" value="<?=$image;?>" />
  	</form>
  </div>
  <?php
  }
  ?>
  <div class="right">
  	<?php if(isset($thumb)) { ?>
  	<img class="email_thumb" src="<?=$thumb;?>" />
  	<?php } ?>	
  </div>
</div>