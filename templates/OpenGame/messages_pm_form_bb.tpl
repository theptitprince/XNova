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
</tr>
<tr>

</tr>
<tr>
	<th colspan="2"><input type="reset" value="{msg_reset}" /></th>
</tr><tr>
	<th colspan="2"><input type="submit" value="{msg_send}" size="20" style="font-weight:bold" onClick="this.form.submit();this.disabled=true;this.value='{msg_wait}'"/></th>
</tr><tr>
	<th colspan="2">{msg_bb_title}<br />{msg_bb_intro}<br /><br /><img src="images/emoticones/Smile.png" alt=":Smile:" title=":Smile:"> :Smile: <img src="images/emoticones/cool.png" alt=":cool:" title=":cool:"> :cool: <img src="images/emoticones/grrr.png" alt=":grrr:" title=":grrr:"> :grrr: <img src="images/emoticones/love.png" alt=":love:" title=":love:"> :love: <img src="images/emoticones/msn.png" alt=":msn:" title=":msn:"> :msn: <img src="images/emoticones/Oo.png" alt=":Oo:" title=":Oo:"> :Oo: <img src="images/emoticones/perdu.png" alt=":perdu:" title=":perdu:"> :perdu: <img src="images/emoticones/wink.png" alt=":wink:" title=":wink:"> :wink: <img src="images/emoticones/wow.png" alt=":wow:" title=":wow:"> :wow:<hr />{msg_bb_bold} = [b]{msg_bb_text}[/b]<br />{msg_bb_underline} = [u]{msg_bb_text}[/u]<br />{msg_bb_italic} = [i]{msg_bb_text}[/i]<br />{msg_bb_image} = [img]https://exemple.fr/image.png[/img]</th>
</tr>
</table>
</form>
</center>