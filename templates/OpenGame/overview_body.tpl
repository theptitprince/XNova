<style type="text/css">
/* Vue generale, disposition d'OGame classique : lune a gauche, planete au centre, autres planetes a droite (2 par ligne).
   Largeurs figees : la planete ne bouge pas, seule la colonne des colonies s'allonge vers le bas s'il y en a beaucoup. */
.ov_lune     { width: 90px; height: 250px; vertical-align: top; }
.ov_centre   { width: 206px; height: 250px; vertical-align: top; }
.ov_encours  { height: 40px; overflow: hidden; }
.ov_colonies { width: 204px; padding: 0; vertical-align: top; }
.ov_colos    { width: 204px; table-layout: fixed; }
.ov_colo     { width: 50%; text-align: center; vertical-align: top; font-size: 9px; padding: 2px 0; } /* vignettes 89 px : table.s du skin */
.ov_texte    { display: block; width: 96px; margin: 0 auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ov_nomlune  { display: block; width: 86px; margin: 0 auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
<script type="text/javascript">var xnova_heure_serveur = {server_clock};</script>
<script language="JavaScript" type="text/javascript" src="scripts/time.js"></script>
<br>
<table width="519">
	<tr><td class="c" colspan="4"><a href="overview.php?mode=renameplanet" title="{planet_menu}">{planet_label} "{planet_name}"</a> ({user_username})</td></tr>
	{have_new_message}
	{have_new_level_mineur}
	{have_new_level_raid}
	<tr><th>{server_time}</th>
	<th colspan="3"><div id="dateheure"></div></th></tr>
	<tr><th>{members_online}</th>
	<th colspan="3">{number_members_online}</th></tr>
	{news_frame}
	<tr><td colspan="4" class="c">{events}</td>
	</tr>
	{fleet_list}
	<tr><th class="ov_lune">{moon_img}<span class="ov_nomlune" title="{moon}">{moon}</span></th>
	<th colspan="2" class="ov_centre"><img src="{dpath}planeten/{planet_image}.jpg" height="200" width="200"><div class="ov_encours">{building}</div></th>
	<th class="ov_colonies">{colonies_list}</th></tr>
	<tr><th>{diameter_label}</th>
	<th colspan="3">{planet_diameter} km (<a title="{developed_fields}">{planet_field_current}</a> / <a title="{max_eveloped_fields}">{planet_field_max}</a> {fields})</th></tr>
	<tr><th>{developed_fields}</th>
	<th colspan="3" align="center"><div  style="border: 1px solid rgb(153, 153, 255); width: 400px;"><div  id="CaseBarre" style="background-color: {case_barre_barcolor}; width: {case_barre}px;"><font color="#CCF19F">{case_pourcentage}</font></div></div></th></tr>
	<tr><th>{ov_off_level}</th><th colspan="3" align="center"><table border="0" width="100%"><tbody><tr>
		<td align="center" width="50%" style="background-color: transparent;"><b>{ov_off_mines} : {lvl_minier}</b></td>
		<td align="center" width="50%" style="background-color: transparent;"><b>{ov_off_raids} : {lvl_raid}</b></td></tr></tbody></table></th></tr>
	<tr><th>{ov_off_expe}</th>
	<th colspan="3" align="center"><table border="0" width="100%"><tbody><tr>
		<td align="center" width="50%" style="background-color: transparent;"><b>{ov_off_mines} : {xpminier} / {lvl_up_minier}</b></td>
		<td align="center" width="50%" style="background-color: transparent;"><b>{ov_off_raids} : {xpraid} / {lvl_up_raid}</b></td></tr></tbody></table></th></tr>
	<tr><th>{temperature_label}</th>
	<th colspan="3">{ov_temp_from} {planet_temp_min}{ov_temp_unit} {ov_temp_to} {planet_temp_max}{ov_temp_unit}</th></tr>
	<tr><th>{position_label}</th>
	<th colspan="3"><a href="galaxy.php?mode=0&galaxy={galaxy_galaxy}&system={galaxy_system}">[{galaxy_galaxy}:{galaxy_system}:{galaxy_planet}]</a></th></tr>
	<tr><th>{ov_local_cdr}</th>
	<th colspan="3">{metal_label} : {metal_debris} / {crystal_label} : {crystal_debris}{get_link}</th></tr>
	<tr><th>{points_label}</th>
	<th colspan="3"><table border="0" width="100%"><tbody><tr>
		<td align="right" width="50%" style="background-color: transparent;"><b>{ov_pts_build} :</b></td>
		<td align="left" width="50%" style="background-color: transparent;"><b>{user_points}</b></td></tr>
		<tr><td align="right" width="50%" style="background-color: transparent;"><b>{ov_pts_fleet} :</b></td>
		<td align="left" width="50%" style="background-color: transparent;"><b>{user_fleet}</b></td></tr>
		<tr><td align="right" width="50%" style="background-color: transparent;"><b>{ov_pts_reche} :</b></td>
		<td align="left" width="50%" style="background-color: transparent;"><b>{player_points_tech}</b></td></tr>
		<tr><td align="right" width="50%" style="background-color: transparent;"><b>{ov_pts_total} :</b></td>
		<td align="left" width="50%" style="background-color: transparent;"><b>{total_points}</b></td></tr>
		<tr><td colspan="2" align="center" width="100%" style="background-color: transparent;"><b>({rank_label} <a href="stat.php?range={u_user_rank}">{user_rank}</a> {of} {max_users})</b></td></tr></tbody></table></th></tr>
	<tr><th>{raids_label}</th>
	<th colspan="3"><table border="0" width="100%"><tbody><tr>
		<td align="right" width="50%" style="background-color: transparent;"><b>{number_of_raids} :</b></td>
		<td align="left" width="50%" style="background-color: transparent;"><b>{raids}</b></td></tr>
		<tr><td align="right" width="50%" style="background-color: transparent;"><b>{raids_win} :</b></td>
		<td align="left" width="50%" style="background-color: transparent;"><b>{raidswin}</b></td></tr>
		<tr><td align="right" width="50%" style="background-color: transparent;"><b>{raids_loose} :</b></td>
		<td align="left" width="50%" style="background-color: transparent;"><b>{raidsloose}</b></td></tr></tbody></table></th></tr>
	{bannerframe}
	{external_tchat_frame}
</table>
<br>
{click_banner}
<br>