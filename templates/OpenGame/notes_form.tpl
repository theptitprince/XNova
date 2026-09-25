
<form action="{php_self}" method=post>
  {inputs}
  <table width=519>
	<tr>
	  <td class=c colspan=2>{title_label}</td>
	</tr>
	<tr>
	  <th>{priority_label}</th>
	  <th>
		<select name=u>
		  {c_options}
		</select>
	  </th>
	</tr>
	<tr>
	  <th>{subject_label}</th>
	  <th>
		<input type="text" name="title" size="30" maxlength="30" value="{title}">
	  </th>
	</tr>
	<tr>
	  <th>{note} (<span id="cntChars">{cnt_chars}</span> / 5000 {characters})</th>
	  <th>
	    <textarea name="text" cols="60" rows="10" onkeyup="javascript:cntchar(5000)">{text}</textarea>
	  </th>
	</tr>
	<tr>
	  <td class="c"><a href="{php_self}">{back}</a></td>
	  <td class="c">
		<input type="reset" value="{reset}">
		<input type="submit" value="{save}">
	  </td>
	</tr>
  </table>
</form>
