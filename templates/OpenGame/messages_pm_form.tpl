<script src="scripts/cntchar.js" type="text/javascript"></script>
<br />
<center>
<form action="messages.php?mode=write&id={id}" method="post">
<table width="519">
<tr>
	<td class="c" colspan="2">{send_message}</td>
</tr><tr>
	<th>{recipient}</th>
	<th><input type="text" name="to" size="40" value="{to}" /></th>
</tr><tr>
	<th>{subject_label}</th>
	<th><input type="text" name="subject" size="40" maxlength="40" value="{subject}" /></th>
</tr><tr>
	<th>{message_label}(<span id="cntChars">0</span> / 5000 {characters})</th>
	<th><textarea name="text" cols="40" rows="10" size="100" onkeyup="javascript:cntchar(5000)">{text}</textarea></th>
</tr><tr>
	<th colspan="2"><input type="submit" value="{envoyer}" /></th>
</tr>
</table>
</form>
</center>