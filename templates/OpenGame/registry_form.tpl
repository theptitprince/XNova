<center>
<br/><br/>
<h2><font size="+3">{registry}</font><br>{servername}</h2>
<form action="" method="post">
<table width="438">
<tbody>
	  <tr>
	    <td colspan="2" class="c"><b>{form}</b></td>
</tr><tr>
	<th width="293">{game_name_label}</th>
    <th width="293"><input name="character" size="20" maxlength="20" type="text" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<tr>
  <th>{neededpass}</th>
  <th><input name="passwrd" size="20" maxlength="20" type="password" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<tr>
  <th>{e_mail}</th>
  <th><input name="email" size="20" maxlength="40" type="text" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<tr>
  <th>{main_planet}</th>
  <th><input name="planet" size="20" maxlength="20" type="text" onKeypress="
     if (event.keyCode==60 || event.keyCode==62) event.returnValue = false;
     if (event.which==60 || event.which==62) return false;"></th>
</tr>
<tr>
  <th>{sex_label}</th>
  <th><select name="sex">
		<option value="">{undefined}</option>
		<option value="M">{male}</option>
		<option value="F">{female}</option>
		</select></th>
</tr>
<tr>
  <td height="20" colspan="2"></td>
  </tr>
<tr>
  <th><input name="rgt" type="checkbox">
    {accept}</th>
  <th><input name="submit" type="submit" value="{signup}"></th>
</tr>
</table>
</form>
</center>