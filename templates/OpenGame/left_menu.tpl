<div id='leftmenu'>
<script language="JavaScript">
function f(target_url,win_name,win_w,win_h) {
  win_w = win_w || 550;
  win_h = win_h || 280;
  var new_win = window.open(target_url,win_name,'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width='+win_w+',height='+win_h+',top=0,left=0');
  if (new_win) new_win.focus(); // popup bloquee : pas d'erreur
}
</script>
<body  class="style" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<center>
<div id='menu'>
<br>
<table width="130" cellspacing="0" cellpadding="0">
<tr>
	<td colspan="2" style="border-top: 1px #545454 solid"><div><center>{servername}<br>(<a href="changelog.php" target={mf}><font color=red>{xnova_release}</font></a>)<center></div></td>
</tr><tr>
	<td colspan="2" background="{dpath}img/bg1.gif"><center>{devlp}</center></td>
</tr><tr>
	<td colspan="2"><div><a href="overview.php" accesskey="g" target="{mf}">{overview}</a></div></td>
</tr><tr>

	<td height="1px" colspan="2" style="background-color:#FFFFFF"></td>
</tr><tr>
	<td colspan="2"><div><a href="buildings.php" accesskey="b" target="{mf}">{buildings_label}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="buildings.php?mode=research" accesskey="r" target="{mf}">{research_label}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="buildings.php?mode=fleet" accesskey="f" target="{mf}">{shipyard}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="buildings.php?mode=defense" accesskey="d" target="{mf}">{defense_label}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="officier.php" accesskey="o" target="{mf}">{officiers}</a></div></td>
</tr></tr>{marchand_link}<tr><tr>

	<td colspan="2" background="{dpath}img/bg1.gif"><center>{navig}</center></td>
</tr><tr>
	<td colspan="2"><div><a href="alliance.php" accesskey="a" target="{mf}">{alliance_label}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="fleet.php" accesskey="t" target="{mf}">{fleet_label}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="messages.php" accesskey="c" target="{mf}">{messages_label}</a></div></td>
</tr><tr>

	<td colspan="2" background="{dpath}img/bg1.gif"><center>{observ}</center></td>
</tr><tr>
	<td colspan="2"><div><a href="galaxy.php?mode=0" accesskey="s" target="{mf}">{galaxy_label}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="imperium.php" accesskey="i" target="{mf}">{imperium}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="resources.php" accesskey="r" target="{mf}">{resources_label}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="techtree.php" accesskey="g" target="{mf}">{technology}</a></div></td>
</tr><tr>

	<td height="1px" colspan="2" style="background-color:#FFFFFF"></td>
</tr><tr>
	<td colspan="2"><div><a href="records.php" accesskey="3" target="{mf}">{records}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="stat.php?range={user_rank}" accesskey="k" target="{mf}">{statistics}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="search.php" accesskey="b" target="{mf}">{search}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="banned.php" accesskey="3" target="{mf}">{blocked}</a></div></td>
</tr>{announce_link}<tr>


	<td colspan="2" background="{dpath}img/bg1.gif"><center>{commun}</center></td>
	</tr><tr>
	<td colspan="2"><div><a href="buddy.php" accesskey="c" target="{mf}">{buddylist}</a></div></td>
</tr></tr>{notes_link}<tr><tr>
	<td colspan="2"><div><a href="chat.php" accesskey="a" onClick="f('chat.php', 'Chat', 700, 550); return false;">{chat}</a></div></td>
</tr>{forum_link}<tr>
	<td colspan="2"><div><a href="add_declare.php" accesskey="1" target="{mf}">{multi}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="rules.php"  accesskey="c" target="{mf}">{rules_label}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="contact.php" accesskey="3" target="{mf}" >{contact}</a></div></td>
</tr><tr>
	<td colspan="2"><div><a href="options.php" accesskey="o" target="{mf}">{options_label}</a></div></td>
</tr>
	{admin_link}
<tr>
</tr>
	{added_link}
<tr>
	<td colspan="2"><div><a href="javascript:top.location.href='logout.php'" accesskey="s" style="color:red">{logout}</a></div></td>
</tr><tr>
	<td colspan="2" background="{dpath}img/bg1.gif"><center>{infog}</center></td>
</tr>
	{server_info}
<tr>
	<td colspan="2"><div><center><a href="credit.php" accesskey="T" target="{mf}">XNova Team</a><br>&copy; Copyright 2008</center></div></td>
</tr>
</table>
</div>
</center>
</body>
</div>