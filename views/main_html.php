<?php
/* ----------------------------------------------------------------------
 * app/widgets/count/views/main_html.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2010 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
 
 	$po_request			= $this->getVar('request');
	$va_item_list			= $this->getVar('item_list');
	$vs_table_num			= $this->getVar('table_num');
	$vs_table_display		= $this->getVar('table_display');
	$vs_status_display		= $this->getVar('status_display');
	$vs_widget_id			= $this->getVar('widget_id');
	$vn_height_px			= $this->getVar('height_px');
    $set_to_display         = $this->getVar('set_to_display');
    $set_label = $this->getVar('set_label');
?>

<div class="dashboardWidgetContentContainer">
	<div class="dashboardWidgetHeading"><small>Ensemble</small> <b><a href="http://sacem.ideesculture.fr/gestion/index.php/manage/sets/SetEditor/Edit/Screen17/set_id/<?php print $set_to_display; ?>"><?php print $set_label."</a></b> (".sizeof($va_item_list)." archives)"; ?></div>
	<div class="dashboardWidgetScrollMedium"><ul>
<?php
	foreach($va_item_list as $vn_id => $va_record) {
		print "<li><a href=\"".caEditorUrl($po_request, $vs_table_num, $vn_id)."\">".(strlen($va_record["display"])>0 ? $va_record["display"] : _t("[BLANK]"))."</a> <small>".$va_record["idno"]."</small></li>\n";
	}
?>
	</ul></div>
</div>