<?php
/* ----------------------------------------------------------------------
 * recordsInSetWidget.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2010-2016 Whirl-i-Gig
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html)
 * ----------------------------------------------------------------------
 * This Widget :
 * Created by idéesculture (Gautier Michelin), 12/2017.
 */
 
 	require_once(__CA_LIB_DIR__.'/ca/BaseWidget.php');
 	require_once(__CA_LIB_DIR__.'/ca/IWidget.php');
 	require_once(__CA_LIB_DIR__.'/core/Db.php');
	require_once(__CA_LIB_DIR__.'/core/Datamodel.php');
	require_once(__CA_MODELS_DIR__."/ca_lists.php");
    require_once(__CA_MODELS_DIR__."/ca_sets.php");
 
	class recordsInSetWidget extends BaseWidget implements IWidget {
		# -------------------------------------------------------
		private $opo_config;
		private $opo_datamodel;

		private $opa_table_display_names;
		private $opa_status_display_names;
		
		static $s_widget_settings = array(	);
		
		# -------------------------------------------------------
		public function __construct($ps_widget_path, $pa_settings) {
			$this->title = "Ensemble";
			$this->description = "Affiche les archives contenues dans un ensemble";
			parent::__construct($ps_widget_path, $pa_settings);
			
			$this->opo_config = Configuration::load($ps_widget_path.'/conf/recordsInSet.conf');
			$this->opo_datamodel = Datamodel::load();
			# -- get status values
			$t_lists = new ca_lists();
			$va_statuses = caExtractValuesByUserLocale($t_lists->getItemsForList("workflow_statuses"));
			$va_status_info = array();
			$va_status_values = array();
			foreach($va_statuses as $i => $va_info){
				$va_status_info[$va_info["item_value"]] = $va_info["name_singular"];
				$va_status_values[] = $va_info["item_value"];
			}
			
			$this->opa_status_display_names = $va_status_info;
			$this->opa_status_values = $va_status_values;

			$this->opa_table_display_names = array(
				'ca_objects' => _t('Objects'),
				'ca_entities' => _t('Entities'),
				'ca_places' => _t('Places'),
				'ca_occurrences' => _t('Occurrences'),
				'ca_sets' => _t('Sets'),
				'ca_collections' => _t('Collections'),
				'ca_object_representations' => _t('Object representations'),
				'ca_object_lots' => _t('Object lots'),
			);

			foreach($this->opa_table_display_names as $vs_table => $vs_display){
					foreach(BaseWidget::$s_widget_settings['recordsInSetWidget']["display_type"]["options"] as $vs_setting_display => $vs_setting_table){
						if($vs_setting_table==$vs_table){
							unset(BaseWidget::$s_widget_settings['recordsInSetWidget']["display_type"]["options"][$vs_setting_display]);
						}
					}
			}
		}
		# -------------------------------------------------------
		/**
		 * Override checkStatus() to return true
		 */
		public function checkStatus() {
			$vb_available = false;
			if($this->getRequest()){
				foreach($this->opa_table_display_names as $vs_table => $vs_display){
						$vb_available = true;
				}
			}

			$vb_available = $vb_available && ((bool)$this->opo_config->get('enabled'));

			return array(
				'description' => $this->getDescription(),
				'errors' => array(),
				'warnings' => array(),
				'available' => $vb_available
			);
		}
		# -------------------------------------------------------
		public function renderWidget($ps_widget_id, &$pa_settings) {
			parent::renderWidget($ps_widget_id, $pa_settings);
			global $g_ui_locale_id;

            $set_label = "";
            $set_id = $pa_settings['set_to_display'];
            if($set_id) {
                $t_set = new ca_sets($set_id);
                $set_label = $t_set->get("ca_sets.preferred_labels");
            }
			//return $this->opo_view->render('main_html.php');
			if ($t_table = $this->opo_datamodel->getInstanceByTableName("ca_objects", true)) {

				$vo_db = new Db();

				$vs_deleted_sql = '';
				if ($t_table->hasField('deleted')) {
					$vs_deleted_sql = " AND (t.deleted = 0) ";
				}
				
				$vs_sql = "
					SELECT
						t.{$t_table->primaryKey()},
						lt.{$t_table->getLabelDisplayField()},
						lt.locale_id
					FROM
					    ca_set_items AS casi 
					LEFT JOIN
						{$t_table->tableName()} AS t ON casi.table_num=57 AND casi.row_id = t.{$t_table->primaryKey()}
					LEFT JOIN
						{$t_table->getLabelTableName()} AS lt ON t.{$t_table->primaryKey()} = lt.{$t_table->primaryKey()}
					WHERE
					    casi.set_id = {$set_id} 
						{$vs_deleted_sql}
				";
				$qr_records = $vo_db->query($vs_sql);
				$va_item_list = array();

				while($qr_records->nextRow()){

					if (!($vs_label = $qr_records->get($t_table->getLabelTableName().".".$t_table->getLabelDisplayField()))) { $vs_label = '???'; }
					$va_item_list[$qr_records->get($t_table->primaryKey())] = array(
						"display" => $vs_label,
						"locale_id" => $qr_records->get($t_table->getLabelTableName().".locale_id"),
					);
				}
                $this->opo_view->setVar('set_to_display', $pa_settings['set_to_display']);
                $this->opo_view->setVar('set_label', $set_label);
				$this->opo_view->setVar('item_list', $va_item_list);
				$this->opo_view->setVar('table_num', $this->opo_datamodel->getTableNum($t_table->tableName()));
				$this->opo_view->setVar('request', $this->getRequest());
				$this->opo_view->setVar('table_display', $this->opa_table_display_names[$t_table->tableName()]);
				$this->opo_view->setVar('status_display', $this->opa_status_display_names[intval($pa_settings["display_status"])]);

				return $this->opo_view->render('main_html.php');
			}
			
		}
		# -------------------------------------------------------
		/**
		 * Add widget user actions
		 */
		public function hookGetRoleActionList($pa_role_list) {
			$pa_role_list['widget_recordsInSet'] = array();

			return $pa_role_list;
		}
		# -------------------------------------------------------
		/**
		 * Get widget user actions
		 */
		static public function getRoleActionList() {
			return array();
		}
		# -------------------------------------------------------
	}
	
    # Get sets for display
    $t_sets = new ca_sets();
    $va_read_sets = $t_sets->getSets(array("table" => "ca_objects"));
    foreach($va_read_sets as $set_id => $va_set_info){
        $va_set_info=reset($va_set_info);
        $va_options[$va_set_info["set_code"]] = $set_id;
    }
    //$va_read_sets = $t_sets->getSetsForUser(array("table" => "ca_objects", "user_id" => $this->getRequest()->user->getUserID(), "checkAccess" => $this->opa_access_values, "access" => 1, "parents_only" => true));
    //$va_write_sets = $t_sets->getSetsForUser(array("table" => "ca_objects", "user_id" => $this->getRequest()->user->getUserID(), "checkAccess" => $this->opa_access_values, "parents_only" => true));

    # Remove write sets from the read array
    //$va_read_sets = array_diff_key($va_read_sets, $va_write_sets);
    //$va_set_ids = array_merge(array_keys($va_read_sets), array_keys($va_write_sets));

    BaseWidget::$s_widget_settings['recordsInSetWidget'] = array(
			'set_to_display' => array(
				'formatType' => FT_TEXT,
				'displayType' => DT_SELECT,
				'width' => 40, 'height' => 1,
				'takesLocale' => false,
				'default' => 0,
				'options' => $va_options,
				'label' => 'Affiche le contenu de l\'ensemble',
				'description' => 'Ensemble à afficher'
			)
	);