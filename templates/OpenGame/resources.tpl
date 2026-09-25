<br>
<form action="" method="post">
<table width="569">
<tbody>
<tr>
	<td class="c" colspan="5">{production_of_resources_in_the_planet}</td>
</tr><tr>
	<th height="22"></th>
	<th width="60">{metal_label}</th>
	<th width="60">{crystal_label}</th>
	<th width="60">{deuterium_label}</th>
	<th width="60">{energy_label}</th>
</tr><tr>
	<th height="22">{basic_income}</th>
	<td class="k">{metal_basic_income}</td>
	<td class="k">{crystal_basic_income}</td>
	<td class="k">{deuterium_basic_income}</td>
	<td class="k">{energy_basic_income}</td>
</tr>
{resource_row}
<tr>
	<th height="22">{stores_capacity}</th>
	<td class="k">{metal_max}</td>
	<td class="k">{crystal_max}</td>
	<td class="k">{deuterium_max}</td>
	<td class="k"><font color="#00ff00">-</font></td>
	<td class="k"><input name="action" value="{calcule}" type="submit"></td>
</tr><tr>
	<th height="22">Total:</th>
	<td class="k">{metal_total}</td>
	<td class="k">{crystal_total}</td>
	<td class="k">{deuterium_total}</td>
	<td class="k">{energy_total}</td>
</tr>
</tbody>
</table>
</form>
<br>
<table width="569">
<tbody>
<tr>
	<td class="c" colspan="4">{widespread_production}</td>
</tr><tr>
	<th>&nbsp;</th>
	<th>{daily}</th>
	<th>{weekly}</th>
	<th>{monthly}</th>
</tr><tr>
	<th>{metal_label}</th>
	<th>{daily_metal}</th>
	<th>{weekly_metal}</th>
	<th>{monthly_metal}</th>
</tr><tr>
	<th>{crystal_label}</th>
	<th>{daily_crystal}</th>
	<th>{weekly_crystal}</th>
	<th>{monthly_crystal}</th>
</tr><tr>
	<th>{deuterium_label}</th>
	<th>{daily_deuterium}</th>
	<th>{weekly_deuterium}</th>
	<th>{monthly_deuterium}</th>
</tr>
</tbody>
</table>
<br>
<table width="569">
<tbody>
<tr>
	<td class="c" colspan="3">{storage_state}</td>
</tr><tr>
	<th>{metal_label}</th>
	<th>{metal_storage}</th>
	<th width="250">
		<div style="border: 1px solid rgb(153, 153, 255); width: 250px;">
		<div id="AlmMBar" style="background-color: {metal_storage_barcolor}; width: {metal_storage_bar}px;">
		&nbsp;
		</div>
		</div>
	</th>
</tr><tr>
	<th>{crystal_label}</th>
	<th>{crystal_storage}</th>
	<th width="250">
		<div style="border: 1px solid rgb(153, 153, 255); width: 250px;">
		<div id="AlmCBar" style="background-color: {crystal_storage_barcolor}; width: {crystal_storage_bar}px; opacity: 0.98;">
		&nbsp;
		</div>
		</div>
	</th>
</tr><tr>
	<th>{deuterium_label}</th>
	<th>{deuterium_storage}</th>
	<th width="250">
		<div style="border: 1px solid rgb(153, 153, 255); width: 250px;">
		<div id="AlmDBar" style="background-color: {deuterium_storage_barcolor}; width: {deuterium_storage_bar}px;">
		&nbsp;
		</div>
		</div>
	</th>
</tr>
</tbody>
</table>
<br>