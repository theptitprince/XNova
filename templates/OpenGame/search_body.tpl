<br>
 <form action="search.php" method="post">

 <table width="519">
  <tr>
   <td class="c">{search_in_all_game}</td>
  </tr>
  <tr>
   <th>
    <select name="type">
     <option value="playername"{type_playername}>{player_name_label}</option>
     <option value="planetname"{type_planetname}>{planet_name_label}</option>
     <option value="allytag"{type_allytag}>{alliance_tag_label}</option>
     <option value="allyname"{type_allyname}>{alliance_name}</option>
    </select>
    &nbsp;&nbsp;
    <input type="text" name="searchtext" value="{searchtext}"/>
    &nbsp;&nbsp;

    <input type="submit" value="{search}" />
   </th>
  </tr>
</table>
</form>
{search_results}
