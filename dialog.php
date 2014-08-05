<?php
/** ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
$cfg['core']['no_login_form'] = true;
require_once '../../admin/admin.php';



$plugin_name = basename(dirname(__FILE__));
$plugin_url = $cfg['url']['base'].$cfg['directories']['core_plugins_www'].'/'.$plugin_name.'/';
$plugin_file = $plugin_url . basename(__FILE__);

$plugin_path = $cfg['url']['base'].$cfg['directories']['core_plugins_www'].'/'.$plugin_name;

if (!isset($logger)) {
	$logger = new PC_debug();
	$logger->debug = true;
	$logger->set_instant_debug_to_file($cfg['path']['logs'] . 'plugins/pc_shop_b1.html', false, 5);
}
$logger->debug('Starting plugin dialog', 3);

$mod['name'] = 'B1';
$mod['onclick'] = 'mod_pc_shop_b1_click()';
$mod['priority'] = 10;

?>

<script type="text/javascript">

<?php
$js_files = array(
		//'dialog.ln.js'
	);
	foreach ($js_files as $js_file) {
		if (@file_exists($js_file)) {
			include $js_file;
			echo "
";
		}
	}
?>
Ext.namespace('PC.plugins');

function mod_pc_shop_b1_click() {
	var plugin_path = '<?php echo $plugin_path; ?>';
	
	//var dialog = PC.plugins.pc_shop_b1;
	PC.plugin.pc_shop_b1.dialog = {};
	var dialog = PC.plugin.pc_shop_b1.dialog;
	dialog.plugin_file = '<?php echo $plugin_file; ?>';
	dialog.ln = PC.i18n.mod.pc_shop_b1;
	var ln = dialog.ln;
	
	
	
	var b1_panel = new Plugin_pc_shop_b1_panel();
	
	
	dialog.w = new PC.ux.Window({
		//modal: true,
		width: 400,
		//height: 60,
		layout: 'fit',
		layoutConfig: {
			align: 'stretch'
		},
		items: [
			b1_panel
		],
		buttons: [
			{	text: PC.i18n.close,
				handler: function() {
					dialog.w.close();
				}
			}
		]
	});
	dialog.w.show();
}

//ProfisCMS.plugins.pc_shop_b1 = {
PC.plugin.pc_shop_b1 = {
	name: 'B1',
	onclick: mod_pc_shop_b1_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};

</script>