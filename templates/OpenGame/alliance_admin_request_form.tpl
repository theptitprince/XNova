<br>
	<form action="alliance.php?mode=admin&edit=requests&show={id}&sort=0" method="POST">
	<tr>
	  <th colspan=2>{request_from}</th>
	</tr>
	<tr>
	  <th colspan=2>{ally_request_text}</th>
	</tr>
	<tr>
	  <td class="c" colspan=2>{request_responde}</td>
	</tr>
	<tr>
	  <th>&#160;</th>
	  <th><input type="submit" name="accept" value="{accept_label}"></th>
	</tr>
	<tr>
	  <th>{motive_optional} <span id="cntChars">0</span> / 500 {characters}</th>
	  <th><textarea name="text" cols=40 rows=10 onkeyup="javascript:cntchar(500)"></textarea></th>
	</tr>
	<tr>
	  <th>&#160;</th>
	  <th><input type="submit" name="refuse" value="{refuse_label}"></th>
	</tr>
	<tr>
	  <td colspan=2>&#160;</td>
	</tr>
	</form>