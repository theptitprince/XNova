<br>
<table width="519">
	<tr>
	  <td class="c" colspan="9">{members_list_label} ({ammount}: {memberzahl})</td>
	</tr>
	<tr>
	  <th>{number}</th>
	  <th><a href="alliance.php?mode=admin&edit=members&sort1=1&sort2={s}">{name_label}</a></th>
	  <th> </th>
	  <th><a href="alliance.php?mode=admin&edit=members&sort1=2&sort2={s}">{position_label}</a></th>
	  <th><a href="alliance.php?mode=admin&edit=members&sort1=3&sort2={s}">{points_label}</a></th>
	  <th><a href="alliance.php?mode=admin&edit=members&sort1=0&sort2={s}">{coordinated_label}</a></th>
	  <th><a href="alliance.php?mode=admin&edit=members&sort1=4&sort2={s}">{member_from}</a></th>
	  <th><a href="alliance.php?mode=admin&edit=members&sort1=5&sort2={s}">{inactive_since}</a></th>
	  <th>{functions_label}</th>
	</tr>
	{memberslist}
	<tr>
	  <td class="c" colspan="9"><a href="alliance.php?mode=admin&edit=ally">{return_to_overview}</a></td>
	</tr>
</table>
<script src="scripts/wz_tooltip.js" type="text/javascript"></script>
