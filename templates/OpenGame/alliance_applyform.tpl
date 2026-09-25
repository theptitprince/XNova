<br>
<h1>{send_apply}</h1>

<table width=519>
<form action="alliance.php?mode=apply&allyid={allyid}" method=POST>

	<tr>
	  <td class=c colspan=2>{write_to_alliance}</td>
	</tr>
	<tr>
	  <th>{message_label} (<span id="cntChars">{chars_count}</span> / 6000 {characters})</th>
	  <th><textarea name="text" cols=40 rows=10 onkeyup="javascript:cntchar(6000)">{text_apply}</textarea></th>
	</tr>
	<tr>
	  <th>{help}</th>
	  <th><input type=submit name="further" value="{reload}"></th>
	</tr>
	<tr>
	  <th colspan=2><input type=submit name="further" value="{send_label}"></th>
	</tr>
</table>

</form>

<script language="JavaScript" src="js/wz_tooltip.js"></script>