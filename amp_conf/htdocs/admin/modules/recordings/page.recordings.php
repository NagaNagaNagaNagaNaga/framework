<?php /* $Id$ */
//Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
?>

<?php
isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
isset($_REQUEST['promptnum'])?$promptnum = $_REQUEST['promptnum']:$promptnum='';
isset($_REQUEST['recordingdisplay'])?$prompt = $_REQUEST['recordingdisplay']:$prompt='';
isset($_REQUEST['rname'])?$rname = $_REQUEST['rname']:$rname='';
if ($promptnum == null) $promptnum = '1';
$display='recordings';


switch($action) {
	default:
?>
<h2><?php echo _("System Recordings")?></h2>
<form name="prompt" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return prompt_onsubmit();">
        <input type="hidden" name="action" value="recordings_start">
        <input type="hidden" name="display" value="<?php echo $display?>">
        <?php echo _("You can use your extension to record and playback a System Recording.")?><br><br>
        <?php echo _("Please enter your user/extension number:")?>
        <input type="text" size="6" name="cidnum"><br>
        <h6><input name="Submit" type="submit" value="<?php echo _("Continue")?>"></h6><br><br><br><br><br><br>
<script language="javascript">
<!--
var theForm = document.prompt;
theForm.cidnum.focus();
function prompt_onsubmit() {
	defaultEmptyOK = false;
	if (!isInteger(theForm.cidnum.value))
		return warnInvalid(theForm.cidnum, "Please enter your user/extension number:");
		
	return true;
}
-->
</script>
	</form>

<?php
        break;
	case 'recorded':
		$rname=strtr($rname," ", "_"); /* remove any spaces from the name to ensure a happy playground */
		//rename = move in php.  This ensures that someone trying to dial *99 will not hear old recordings.
		rename('/var/lib/asterisk/sounds/'.$_REQUEST['cidnum'].'ivrrecording.wav','/var/lib/asterisk/sounds/custom/'.$rname.'.wav');
		echo '<br><h3>'._("System Recording").' "'.$rname.'" '._("Saved").'!</h3>';

	break;
	case 'delete':
		unlink('/var/lib/asterisk/sounds/custom/'.$prompt.'.wav');
		echo '<br><h3>'._("System Recording").' "'.$prompt.'" '._("Deleted").'!</h3>';
	break;
        case 'recordings_start':
?>

</div>
<div class="rnav">
    <li><a id="<?php echo isset($extdisplay)?'current':''; ?>" href="config.php?display=<?php echo urlencode($display)?>&action=recordings_start&cidnum=<?php echo urlencode($_REQUEST['cidnum']) ?>"><?php echo _("Add Recording")?></a></li>

<?php
//get existing recordings info
$tresults = recordings_list("/var/lib/asterisk/sounds/custom");

if (isset($tresults)){
	foreach ($tresults as $tresult) {
		echo "<li><a id=\"".($recordingdisplay==$tresult ? 'current':'')."\" href=\"config.php?display=".$display."&recordingdisplay={$tresult}&recording_action=edit&action=recordings_start&cidnum=$_REQUEST[cidnum]\">{$tresult}</a></li>";
	}
}

?>
</div>

<div class="content">
<?php
if ($prompt) {
	echo "<h2>"._("Recording").": ".$prompt."</h2>";
} else {
	echo "<h2>"._("Add Recording")."</h2>";
}
?>
<?php
	//if we are trying to edit - let's be nice and give them the recording back
	if (isset($_REQUEST['recording_action']) && $_REQUEST['recording_action'] == 'edit'){
?>
	<p><a href="config.php?display=<?php echo urlencode($display) ?>&recordingdisplay=<?php echo urlencode($prompt) ?>&action=delete"><?php echo _("Delete Recording")?> <?php echo $prompt; ?></a></p>
<?php
		//copy('/var/lib/asterisk/sounds/custom/'.$prompt.'.wav','/var/lib/asterisk/sounds/ivrrecording.wav');
		copy('/var/lib/asterisk/sounds/custom/'.$prompt.'.wav','/var/lib/asterisk/sounds/'.$_REQUEST['cidnum'].'ivrrecording.wav');

		echo '<h5>'._('Dial *99 to listen to your current recording - click continue if you wish to re-use it.').'</h5>';
	}
?>
<h5><?php echo _("Step 1: Record")?></h5>
<p>
	<?php echo _("Using your phone,")?> <a href="#" class="info"><?php echo _("dial *77")?><span><?php echo _("Start speaking at the tone. Hangup when finished.")?></span></a> <?php echo _("and speak the message you wish to record.")?>
</p>
<p>
	<form enctype="multipart/form-data" name="upload" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST"/>
		<?php echo _('Alternatively, upload a recording in')?> <a href="#" class="info"><?php echo _(".wav format")?><span><?php echo _("The .wav file _must_ have a sample rate of 8000Hz")?></span></a>:<br>
		<input type="hidden" name="display" value="<?php echo $display?>">
		<input type="hidden" name="promptnum" value="<?php echo $promptnum?>">
		<input type="hidden" name="action" value="recordings_start">
                <input type="hidden" name="cidnum" value="<?php echo $_REQUEST['cidnum'];?>">
		<input type="file" name="ivrfile"/>

		<input type="button" value="<?php echo _("Upload")?>" onclick="document.upload.submit(upload);alert('<?php echo _("Please wait until the page reloads.")?>');"/>
	</form>
<?php
if (isset($_FILES['ivrfile']['tmp_name']) && is_uploaded_file($_FILES['ivrfile']['tmp_name'])) {
	move_uploaded_file($_FILES['ivrfile']['tmp_name'], "/var/lib/asterisk/sounds/".$_REQUEST['cidnum']."ivrrecording.wav");
	echo "<h6>"._("Successfully uploaded")." ".$_FILES['ivrfile']['name']."</h6>";
}
?>
</p>
<form name="prompt" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return rec_onsubmit();">
<input type="hidden" name="action" value="recorded">
<input type="hidden" name="cidnum" value="<?php echo $_REQUEST['cidnum'];?>">
<input type="hidden" name="promptnum" value="<?php echo $promptnum?>">
<input type="hidden" name="display" value="<?php echo $display?>">
<h5><?php echo _("Step 2: Verify")?></h5>
<p>
	<?php echo _("After recording or uploading, <em>dial *99</em> to listen to your recording.")?>
</p>
<p>
	<?php echo _("If you wish to re-record your message, dial *77 again.")?>
</p>
<h5><?php echo _("Step 3: Name")?> </h5>
<table style="text-align:right;">
<tr valign="top">
	<td valign="top"><?php echo _("Name this Recording")?>: </td>
	<td style="text-align:left"><input type="text" name="rname" value="<?php echo $prompt ?>"></td>
</tr>
</table>
<h6><?php echo _("Click \"SAVE\" when you are satisfied with your recording")?><input name="Submit" type="submit" value="<?php echo _("Save")?>"></h6>
<script language="javascript">
<!--
var theForm = document.prompt;
function rec_onsubmit() {
	defaultEmptyOK = false;
	if (!isAlphanumeric(theForm.rname.value))
		return warnInvalid(theForm.rname, "Please enter a valid Name for this System Recording");
}
-->
</script>
</form>

<?php
	break;
?>


<?php
}
?>

