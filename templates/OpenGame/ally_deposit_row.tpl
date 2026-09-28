<tr>
	<th>{owner}</th>
	<th>{ships}</th>
	<th>{end}</th>
	<th>{cost}</th>
	<th>
	<form action="allydeposit.php" method="post">
	<input type="hidden" name="fleetid" value="{fleet_id}">
	<input type="text" name="hours" size="3" value="{hours}"> {hours_label}<br>
	<input type="submit" value="{depot_supply}"{disabled}>
	</form>
	</th>
</tr>
