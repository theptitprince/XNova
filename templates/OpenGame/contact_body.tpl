<center>
<br><br>
<form action="contact.php" method="post">
<input type="hidden" name="ft" value="{ctc_ft}">
<input type="hidden" name="fs" value="{ctc_fs}">
<div style="position:absolute; left:-5000px;" aria-hidden="true"><input type="text" name="website" value="" tabindex="-1" autocomplete="off"></div>
<table width="569">
<tbody>
<tr>
	<td colspan="2" class="c"><b>{ctc_title}</b></td>
</tr><tr>
	<th colspan="2"><font color="orange">{ctc_intro}</font></th>
</tr>
{ctc_result}
<tr>
	<th width="150">{ctc_form_name}</th>
	<th><input type="text" name="name" value="{ctc_value_name}" size="40" maxlength="64"></th>
</tr><tr>
	<th>{ctc_form_email}</th>
	<th><input type="text" name="email" value="{ctc_value_email}" size="40" maxlength="128"></th>
</tr><tr>
	<th>{ctc_form_subject}</th>
	<th><input type="text" name="subject" value="{ctc_value_subject}" size="40" maxlength="100"></th>
</tr><tr>
	<th>{ctc_form_message}</th>
	<th><textarea name="message" cols="50" rows="10">{ctc_value_message}</textarea></th>
</tr><tr>
	<th colspan="2"><input type="submit" value="{ctc_form_send}"></th>
</tr>
</tbody>
</table>
</form>
</center>
