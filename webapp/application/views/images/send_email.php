<div id="send_email">
<?php
	if(isset($form_error)) {
?>
		<br /><br />
		<a href="javascript:history.go(-1)">Go back</a> and fill in all the form fields before sending the email.
		
<?php
	} else if(isset($sent)){
		if($sent) {
?>
<br /><br />
Your email has been sent.
<br /><br />
<a id="close" href="#">Close</a>
		
<?php
		} else {
			echo "<br /><br />An error occured while trying to send your email.";
			echo '<br /><br />';
			echo '<a id="close" href="#">Close</a>';
		}
	}
?>
</div>