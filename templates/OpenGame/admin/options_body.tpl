<br /><br />
<h2>{adm_opt_title}</h2>
<form action="" method="post">
<input type="hidden" name="opt_save" value="1">
<table width="650" style="color:#FFFFFF">
<tbody>
<tr>
	<td class="c" colspan="2">{adm_opt_game_settings}</td>
</tr><tr>
	<th width="45%">{adm_opt_game_name}</th>
	<th><input name="game_name" size="40" value="{game_name}" type="text"></th>
</tr><tr>
	<th>{adm_opt_menu_link_enable}</th>
	<th><input name="enable_link_"{enable_link} type="checkbox"></th>
</tr><tr>
	<th>{adm_opt_menu_link_text}</th>
	<th><input name="name_link_" size="40" value="{name_link}" type="text"></th>
</tr><tr>
	<th>{adm_opt_menu_link_url}</th>
	<th><input name="url_link_" size="40" value="{url_link}" type="text"></th>
</tr><tr>
	<th>{adm_opt_game_gspeed}</th>
	<th><input name="game_speed" size="12" value="{game_speed}" type="text"></th>
</tr><tr>
	<th>{adm_opt_game_fspeed}</th>
	<th><input name="fleet_speed" size="12" value="{fleet_speed}" type="text"></th>
</tr><tr>
	<th>{adm_opt_noob}</th>
	<th><input name="noobprotection"{noobprotection} type="checkbox"></th>
</tr><tr>
	<th>{adm_opt_noob_time}</th>
	<th><input name="noobprotectiontime" size="12" value="{noobprotectiontime}" type="text"></th>
</tr><tr>
	<th>{adm_opt_noob_multi}</th>
	<th><input name="noobprotectionmulti" size="12" value="{noobprotectionmulti}" type="text"></th>
</tr><tr>
	<th>{stat_settings_desc}</th>
	<th>{stat_desc} <input name="stat_settings" size="12" value="{stat_settings}" type="text"> {stat_units}</th>
</tr><tr>
	<th>{adm_opt_game_pspeed}</th>
	<th><input name="resource_multiplier" size="12" value="{resource_multiplier}" type="text"></th>
</tr><tr>
	<th>{adm_opt_game_forum}</th>
	<th><input name="forum_url" size="40" maxlength="254" value="{forum_url}" type="text"></th>
</tr><tr>
	<th>{adm_opt_game_online}</th>
	<th><input name="closed"{closed} type="checkbox"></th>
</tr><tr>
	<th>{adm_opt_game_offreaso}</th>
	<th><textarea name="close_reason" cols="60" rows="5">{close_reason}</textarea></th>
</tr><tr>
	<td class="c" colspan="2">{messages_settings}</td>
</tr><tr>
	<th>{bbcode_settings}</th>
	<th><input name="bbcode_field"{enable_bbcode} type="checkbox"></th>
</tr><tr>
	<td class="c" colspan="2">{multi_bot_settings}</td>
</tr><tr>
	<th>{bot_active}</th>
	<th><input name="bot_enable"{enable_bot} type="checkbox"></th>
</tr><tr>
	<th>{bot_name_multi}</th>
	<th><input name="name_bot" size="40" value="{bot_name}" type="text"></th>
</tr><tr>
	<th>{bot_adress_multi}</th>
	<th><input name="adress_bot" size="40" value="{bot_adress}" type="text"></th>
</tr><tr>
	<th>{bot_ban_duration}</th>
	<th><input name="duration_ban" size="12" value="{ban_duration}" type="text"></th>
</tr><tr>
	<td class="c" colspan="2">{adm_opt_plan_settings}</td>
</tr><tr>
	<th>{adm_opt_plan_initial}</th>
	<th><input name="initial_fields" size="12" value="{initial_fields}" type="text"> {adm_opt_fields_unit}</th>
</tr><tr>
	<th>{adm_opt_plan_base_inc}{metal_label}</th>
	<th><input name="metal_basic_income" size="12" value="{metal_basic_income}" type="text"> {adm_opt_per_hour}</th>
</tr><tr>
	<th>{adm_opt_plan_base_inc}{crystal_label}</th>
	<th><input name="crystal_basic_income" size="12" value="{crystal_basic_income}" type="text"> {adm_opt_per_hour}</th>
</tr><tr>
	<th>{adm_opt_plan_base_inc}{deuterium_label}</th>
	<th><input name="deuterium_basic_income" size="12" value="{deuterium_basic_income}" type="text"> {adm_opt_per_hour}</th>
</tr><tr>
	<th>{adm_opt_plan_base_inc}{energy_label}</th>
	<th><input name="energy_basic_income" size="12" value="{energy_basic_income}" type="text"> {adm_opt_per_hour}</th>
</tr><tr>
	<td class="c" colspan="2">{adm_opt_control_pages}</td>
</tr><tr>
	<th>{enable_the_anounces}</th>
	<th><input name="enable_announces_"{enable_announces} type="checkbox"></th>
</tr><tr>
	<th>{enable_the_marchand}</th>
	<th><input name="enable_marchand_"{enable_marchand} type="checkbox"></th>
</tr><tr>
	<th>{enable_the_notes}</th>
	<th><input name="enable_notes_"{enable_notes} type="checkbox"></th>
</tr><tr>
	<td class="c" colspan="2">{adm_opt_game_oth_info}</td>
</tr><tr>
	<th>{adm_opt_game_oth_bann}</th>
	<th><input name="bannerframe"{bannerframe} type="checkbox"> ({adm_opt_warning1})</th>
</tr><tr>
	<th>{adm_opt_game_oth_news}</th>
	<th><input name="newsframe"{newsframe} type="checkbox"></th>
</tr><tr>
	<th colspan="2"><textarea name="NewsText" cols="80" rows="5">{news_text_val}</textarea></th>
</tr><tr>
	<th>{adm_opt_game_oth_chat}</th>
	<th><input name="chatframe"{chatframe} type="checkbox"></th>
</tr><tr>
	<th colspan="2"><textarea name="ExternChat" cols="80" rows="5">{ext_tchat_val}</textarea></th>
</tr><tr>
	<th>{adm_opt_game_oth_adds}</th>
	<th><input name="googlead"{googlead} type="checkbox"></th>
</tr><tr>
	<th colspan="2"><textarea name="GoogleAds" cols="80" rows="5">{google_ad_val}</textarea></th>
</tr><tr>
	<th>{adm_opt_game_debugmod}</th>
	<th><input name="debug"{debug} type="checkbox"></th>
</tr><tr>
	<th>{banner}</th>
	<th><input name="banner_source_post" size="40" value="{banner_source_post}" type="text"></th>
</tr><tr>
	<th colspan="2"><img src="{banner_source_post}" alt="" title="{banner_source_post}"></th>
</tr><tr>
	<th colspan="2"><input value="{adm_opt_btn_save}" type="submit"></th>
</tr>
</tbody>
</table>
</form>
