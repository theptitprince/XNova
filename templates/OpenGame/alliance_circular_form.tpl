<br>
<form action="?a=17&sendmail=1" method=post>
  <table width=519>
	<tr>
	  <td class=c colspan=2>{send_circular_mail_label}</td>
	</tr>
	<tr>
	  <th>{destiny}</th>
	  <th>
		<select name=r>
		  {r_list}
		</select>
	  </th>
	</tr>

	<tr>
	  <th>{text_mail} (<span id="cntChars">0</span> / 5000 {characters})</th>
	  <th>
	    <textarea name="text" cols="60" rows="10" onkeyup="javascript:cntchar(5000)"></textarea>
	  </th>
	</tr>
	<tr>
	  <td class="c"><a href="alliance.php">{back}</a></td>
	  <td class="c">
		<input type="reset" value="{clear}">
		<input type="submit" value="{send_label}">
	  </td>
	</tr>
  </table>
</form>
