<br><br>
<h2>{declare_title}</h2>
<form action="add_declare.php" method="post">
<input type="hidden" name="mode" value="addit">
<table width="519">
<tr>
	<td class="c" colspan="2">{declare_title}</td>
</tr><tr>
	<th colspan="2" style="font-weight: normal; padding: 6px;">{declare_text}</th>
</tr><tr>
	<th>{declare_player_1}</th>
	<th><input name="dec1" type="text" value="" maxlength="64" /></th>
</tr><tr>
	<th>{declare_player_2}</th>
	<th><input name="dec2" type="text" value="" maxlength="64" /></th>
</tr><tr>
	<th>{declare_player_3}</th>
	<th><input name="dec3" type="text" value="" maxlength="64" /></th>
</tr><tr>
	<th>{declare_reason}</th>
	<th><input name="reason" type="text" value="" maxlength="255" /></th>
</tr><tr>
	<th colspan="2"><input type="submit" value="{declare_send}" /></th>
</tr>
</table>
</form>
