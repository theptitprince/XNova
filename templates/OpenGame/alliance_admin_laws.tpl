<br>
<table width=519><tr><td class=c colspan=11>{configure_laws}</td></tr>

{list}


</table>

<br>

<form action="alliance.php?mode=admin&edit=rights&add=name" method=POST>
<table width=519>
	<tr>
	  <td class=c colspan=2>{range_make}</td>
	</tr>
	<tr>
	  <th>{range_name}</th>
	  <th><input type=text name="newrangname" size=20 maxlength=30></th>
	</tr>
	<tr>
	  <th colspan=2><input type=submit value="{make}"></th>
	</tr>
</form>
</table>

<form action="alliance.php?mode=admin&edit=rights" method=POST>
<table width=519>
	<tr>
	  <td class=c colspan=2>{law_leyends}</td>
	</tr>
	<tr>
	  <th><img src=images/r1.png></th>
	  <th>{alliance_dissolve}</th>
	</tr>
	<tr>
	  <th><img src=images/r2.png></th>
	  <th>{expel_users}</th>
	</tr>
	<tr>
	  <th><img src=images/r3.png></th>
	  <th>{see_the_requests}</th>
	</tr>
	<tr>
	  <th><img src=images/r4.png></th>
	  <th>{see_the_list_members}</th>
	</tr>
	<tr>
	  <th><img src=images/r5.png></th>
	  <th>{check_the_requests}</th>
	</tr>
	<tr>
	  <th><img src=images/r6.png></th>
	  <th>{alliance_admin_label}</th>
	</tr>
	<tr>
	  <th><img src=images/r7.png></th>
	  <th>{see_the_online_list_member}</th>
	</tr>
	<tr>
	  <th><img src=images/r8.png></th><th>{make_a_circular_message}</th>
	</tr>
	<tr>
	  <th><img src=images/r9.png></th><th>{left_hand_text}</th>
	</tr>
	<tr>
	  <td class="c" colspan="2"><a href="alliance.php?mode=admin&edit=ally">{return_to_overview}</a></td>
	</tr>
</form>
</table>
	