<div id="top"></div>

<div id="main_content">
<span id="simple_upload">


<div id="step1">
Step 1 - Upload your image
</div>

<div id="container">
  <div id="upload_form">
    <form enctype="multipart/form-data" action="/select-red-eyes" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="2048000" />
    <input type="hidden" name="PHPSESSID" value="<?=$session_id ?>" />
    <input type="hidden" name="simple_upload" value="true" />
    <input name="filename" type="file" />
    <br /><br />
    <input type="submit" value="Upload" />
    </form>
  </div>

  <div id="upload_text">	
  	<span>Upload an image<br />from your computer</span>
  </div>
</div>

<div id="advancedupload">
Got Adobe Flash installed?<br />
Try our <a href="image-upload">advanced uploader</a>.
<br /><br />
</div>
</span>
</div>



<div id="bottom">
<?php if(isset($_COOKIE['error_msg'])) {?>
<div id="simple_upload_error"><?php echo $_COOKIE['error_msg']?></div>
<?php 
  setcookie("error_msg", "", time() - 3600);
} ?>
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