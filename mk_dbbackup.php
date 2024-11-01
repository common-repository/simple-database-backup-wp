<?php
/*
 * Plugin Name: Simple Database Backup WP
 * Description: Wordpress Database backup
 * Author: DigitalAMC Brothers
 * Text Domain: mk_dbbk
 * Version: 1.0
 * Requires at least: 4.9
 * Tested up to: 4.9
 */
 

defined( 'ABSPATH' ) or exit;

//add admin page
add_action('admin_menu', 'mkdbbk_add_menu');
add_action('admin_enqueue_scripts', 'mkdbbk_admin_enqueue_scripts');

function mkdbbk_admin_enqueue_scripts(){
	
	wp_enqueue_script('mkdbbk-script',  plugins_url('/js/mkdbbk.js', __FILE__ ) , array('jquery'), '', false);
	wp_enqueue_style('mkdbbk-style',  plugins_url('/css/mkdbbk.css', __FILE__ ) , '', '','');
	wp_localize_script('mkdbbk-script', 'admin_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		));
}

function mkdbbk_add_menu(){
add_submenu_page(
					'tools.php',
					'DB backup',
					'DB backup', 
					'manage_options',
					'mk_dbbk',
					'mkdbbk_render_submenu_page',
					'dashicons-format-video'
				);
}

function mkdbbk_render_submenu_page(){ ?>
		<div class="mk_dbbackup_main">
			<div class='notice_mkdbbk'></div>
			<div class="loader" style="display:none"><center><img src="<?php echo plugin_dir_url( __FILE__ ).'loader.gif'  ?>" /></center></div>
				<form method="post" id="dbbackupform" action="" enctype="multipart/form-data">
						<div>					
							<input type="submit" class="mk_dbbk_button button button-primary" name="mk_dbbk_button" value="Start Backup" />
						</div>
						<?php wp_nonce_field( 'mkbk_field', 'mkbk_field' ); ?>	
				</form>
			<div class="download_list_mk_dbbackup">
				<table class="backup_table">
					<tr>
						<th><?php echo _e('Sr NO','mk_dbbk') ?></th>
						<th><?php echo _e('Date','mk_dbbk') ?></th>
						<th><?php echo _e('Download','mk_dbbk') ?></th>
					</tr>
					<?php 
						$db_list = (array)json_decode(mkdbbk_db_list()); 
						$db_list = array_reverse($db_list); 
						if(!empty($db_list)):
							$key = 1;
							foreach($db_list as $list):
							
								$time = explode('-',$list); 
								$time1 = explode('.',$time[1]);
								$date = date('Y-m-d G:i:s',$time1[0]);								
																
								$url = site_url().'/wp-content/uploads/mk_dbbackup/'.$list;
								
								echo "<tr>";
									echo "<td>".($key)."</td>";
									echo "<td>".$date."</td>";
									echo "<td><a href='".$url."' download>Download</a></td>";
								echo "</tr>";
								if($list) $key++;
							endforeach;
						else:
							echo '<tr><td colspan="3"><center>No backup found !</center></td></td>';
						endif;
					?>					
				</table>
			</div>
		</div>
<?php	
}

function mkdbbk_mk_dbBackup(){

global $wpdb;

// Get a list of the tables
$tables = $wpdb->get_results('SHOW TABLES');

$upload_dir = wp_upload_dir();
$sql_filename = 'mk_database-' . strtotime(date('Y-m-d G:i:s')) . '.sql';
$file_path = $upload_dir['basedir'] . '/mk_dbbackup/'.$sql_filename;
$dirname = dirname($file_path);
if (!is_dir($dirname)){
    mkdir($dirname, 0755, true);
}
$file = fopen($file_path, 'w');


foreach ($tables as $table){
	
    $table_name = $table->Tables_in_wpp;
    $schema = $wpdb->get_row('SHOW CREATE TABLE ' . $table_name, ARRAY_A);
    fwrite($file, $schema['Create Table'] . ';' . PHP_EOL);

    $rows = $wpdb->get_results('SELECT * FROM ' . $table_name, ARRAY_A);

    if($rows){
        fwrite($file, 'INSERT INTO ' . $table_name . ' VALUES ');

        $total_rows = count($rows);
        $counter = 1;
        foreach ($rows as $row => $fields){
            $line = '';
            foreach ($fields as $key => $value){
                $value = addslashes($value);
                $line .= '"' . $value . '",';
            }

            $line = '(' . rtrim($line, ',') . ')';

            if ($counter != $total_rows){
                $line .= ',' . PHP_EOL;
            }

            fwrite($file, $line);
            $counter++;
        }
        fwrite($file, '; ' . PHP_EOL);
    }
}

fclose($file);
$return['status'] = fclose(___);
$return['filename'] = $sql_filename;
echo json_encode($return);
exit;
}

add_action('wp_ajax_mkdbbk_mk_dbBackup', 'mkdbbk_mk_dbBackup');
add_action('wp_ajax_nopriv_mkdbbk_mk_dbBackup', 'mkdbbk_mk_dbBackup');


function mkdbbk_db_list(){
	$upload_dir = wp_upload_dir();
	$path    = $upload_dir['basedir'] .'/mk_dbbackup/';
	if(is_dir($path)){
		$files = array_diff(scandir($path), array('.', '..'));
		return json_encode($files);
	}else{
		$error = array();
		return json_encode($error);
	}
}
?>