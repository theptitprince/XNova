<br><br>
<h2>{adm_stat_title}</h2>
<form action="statbuilder.php" method="post">
<input type="hidden" name="run" value="1">
<table width="519">
<tr>
	<td class="c" colspan="2">{adm_stat_build}</td>
</tr><tr>
	<th width="50%">{adm_stat_last}</th>
	<th>{stat_last}</th>
</tr><tr>
	<th colspan="2"><input type="submit" value="{adm_stat_run}"></th>
</tr>
</table>
</form>
<br>
<form action="statbuilder.php" method="post">
<input type="hidden" name="save" value="1">
<table width="519">
<tr>
	<td class="c" colspan="2">{adm_stat_auto_title}</td>
</tr><tr>
	<th width="50%">{adm_stat_auto}</th>
	<th><input type="checkbox" name="stat_auto"{auto_checked}{auto_disabled}></th>
</tr><tr>
	<th>{adm_stat_auto_hours}</th>
	<th><input type="text" name="stat_auto_hours" value="{auto_hours}" size="4" maxlength="3"{auto_disabled}> {adm_stat_hours}</th>
</tr><tr>
	<th colspan="2">{adm_stat_auto_note}</th>
</tr><tr>
	<th colspan="2">{auto_submit}</th>
</tr>
</table>
</form>
