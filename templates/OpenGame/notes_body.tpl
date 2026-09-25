
<br>
<form action="{php_self}" method=post>
  <table width=519>
	<tr>
	  <td class=c colspan=4>{notes}</td>
	</tr>
	<tr>
	  <th colspan=4><a href="{php_self}?a=1">{make_new_note}</a></th>
	</tr>
	<tr>
	  <td class=c></td>
	  <td class=c>{date_label}</td>
	  <td class=c>{subject_label}</td>
	  <td class=c>{size}</td>
	</tr>

	{body_list}

<tr>
	  <td colspan=4><input value="{delete_label}" type="submit"></td>
	</tr>
  </table>
</form>
</center>
</body>
</html>
