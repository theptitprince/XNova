<script src="scripts/cntchar.js" type="text/javascript"></script>
<br>
<table width=519>
	<tr>
	  <td class=c colspan=2>{alliance_admin_label}</td>
	</tr>
	<tr>
	  <th colspan=2><a href="?mode=admin&edit=rights">{law_settings}</a></th>
	</tr>
	<tr>
	  <th colspan=2><a href="?mode=admin&edit=members">{members_administrate}</a></th>
	</tr>
	<tr>
	  <th colspan=2><a href="?mode=admin&edit=tag">{change_the_ally_tag}</a></th>
	</tr>
	<!--<img src="{dpath}pic/appwiz.gif" border=0 alt="">-->
	<tr>
	  <th colspan=2><a href="?mode=admin&edit=name">{change_the_ally_name}</a></th>
	</tr>
	<!--<img src="{dpath}pic/appwiz.gif" border=0 alt="">-->
</table>
<br>
<form action="" method="POST">
<input type="hidden" name="t" value="{t}">
<table width=519>
	<tr>
	  <td class="c" colspan=3>{texts}</td>
	</tr>
	<tr>
	  <th><a href="?mode=admin&edit=ally&t=1">{external_text}</a></th>
	  <th><a href="?mode=admin&edit=ally&t=2">{internal_text}</a></th>
	  <th><a href="?mode=admin&edit=ally&t=3">{request_text_label}</a></th>
	</tr>
	<tr>
	  <td class=c colspan=3>{request_type} (<span id="cntChars">0</span> / 5000 {characters})</td>
	</tr>
	<tr>
	  <th colspan=3><textarea name="text" cols=70 rows=15 oninput="cntchar(5000)">{text}</textarea>
	</th>
	</tr>
	<tr>
	  <th colspan=3>
	  <input type="hidden" name=t value={t}><input type="reset" value="{reset}"> 
	  <input type="submit" value="{save}">
	  </th>
	</tr>
</table>
</form>

<br>

<form action="" method="POST">
<table width=519>
	<tr>
	  <td class=c colspan=2>{options_label}</td>
	</tr>
	<tr>
	  <th>{main_page}</th>
	  <th><input type=text name="web" value="{ally_web}" size="70"></th>
	</tr>
	<tr>
	  <th>{alliance_logo}</th>
	  <th><input type=text name="image" value="{ally_image}" size="70"></th>
	</tr>
	<tr>
	  <th>{requests_label}</th>
	  <th>
	  <select name="request_notallow"><option value=1{ally_request_notallow_0}>{no_allow_request}</option>
	  <option value=0{ally_request_notallow_1}>{allow_request}</option></select>
	  </th>
	</tr>
	<tr>
	  <th>{founder_name}</th>
	  <th><input type="text" name="owner_range" value="{ally_owner_range}" size=30></th>
	</tr>
	<tr>
	  <th colspan=2><input type="submit" name="options" value="{save}"></th>
	</tr>
</table>
</form>

{disolve_alliance}
<br>
{transfer_alliance}

