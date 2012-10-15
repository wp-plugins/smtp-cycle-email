<?php
/*
Plugin Name: SMTP Cycle Email
Plugin URI: http://www.cybernetikz.com
Description: Using this plugins, you can send email to different user using various smtp server with spinning text feature.
Version: 0.1
Author: CyberNetikz
Author URI: http://www.cybernetikz.com
License: GPL2
*/



function smtp_cp_spin($s){
	preg_match('#\{(.+?)\}#is',$s,$m);
	if(empty($m)) return $s;

	$t = $m[1];

	if(strpos($t,'{')!==false){
		$t = substr($t, strrpos($t,'{') + 1);
	}

	$parts = explode("|", $t);
	$s = preg_replace("+\{".preg_quote($t)."\}+is", $parts[array_rand($parts)], $s, 1);

	return smtp_cp_spin($s);
}

/*add_action('smtp_ce_event', 'smtp_ce_send_email');

function smtp_ce_activation() {
	
	if ( !wp_next_scheduled( 'smtp_ce_event' ) ) {
		$r = get_option('smtp_ce-recurrence');
		wp_schedule_event( current_time( 'timestamp' ), $r, 'smtp_ce_event');
	}
}
add_action('wp', 'smtp_ce_activation');

function smtp_ce_send_email() {

	//global $wpdb;
	$table_msgq = $wpdb->prefix . "messages_queue";
	$sql = "SELECT * FROM ".$table_msgq." WHERE status='scheduled' ORDER BY id LIMIT 0,1";
	//echo $sql; 
	$msg_info = $wpdb->get_row($sql);
	
	//print_r($msg_info);
	$emailID = $msg_info->id;
	$to = $msg_info->to;
	$subject = $msg_info->subject;
	$body = $msg_info->body;
	
	//echo $subject.$body;
	
	$table_srvr = $wpdb->prefix . "smtp_servers";
	$sql = "SELECT * FROM ".$table_srvr." WHERE active='1' ORDER BY date_last_used ASC LIMIT 0,1";
	//echo $sql; 
	$srvr_info = $wpdb->get_row($sql);
	
	$svrID = $srvr_info->id; 
	$host = $srvr_info->server_address;
	$port = $srvr_info->port;
	$username = $srvr_info->username;
	$password = $srvr_info->password;
	
	smtp_cp_phpmailer_init($host, $port, $username, $password);
	update_option( 'last_execute', time() );
}

function smtp_cp_phpmailer_init($h,$p,$u,$p) {

	global $subject, $to, $body, $svrID, $wpdb, $table_srvr, $emailID, $table_msgq;
	
	if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
		require_once ABSPATH . WPINC . '/class-phpmailer.php';
		require_once ABSPATH . WPINC . '/class-smtp.php';
		$phpmailer = new PHPMailer();
		
		$phpmailer->Mailer = 'smtp';
		$phpmailer->Sender = $phpmailer->From;
		$phpmailer->SMTPSecure = '';
		$phpmailer->Host = $h;
		$phpmailer->Port = $p;
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $u;
		$phpmailer->Password = $p;
		
		$result = wp_mail($to,$subject,$body);
		unset($phpmailer);
		
		
		if ($result){
			$res = $wpdb->update( $table_srvr, array(
			'date_last_used' => time(),
			), array( 'id' => $svrID ) );
			
			$res = $wpdb->update( $table_msgq, array(
			'status' => 'sent',
			), array( 'id' => $emailID ) );
		} else {
			$res = $wpdb->update( $table_srvr, array(
			'active' => '0',
			), array( 'id' => $svrID ) );
		}
	}

}*/

if ( isset($_GET['delete']) and $_GET['delete']=='y' ) {
	if ($_REQUEST['id'] != '')
	{
		$table_name = $wpdb->prefix . $_GET['table'];
		$delete = "DELETE FROM ".$table_name." WHERE id = ".$_REQUEST['id']." LIMIT 1";
		$results = $wpdb->query( $delete );
		if($results)
			$msg = "Delete Successfully!!!"."<br />";
		//echo '<div id="message" class="updated fade">'.$msg.'</div>';
	}
}

function smtp_cp_db_install () {
   global $wpdb;
   //global $vdo_db_version;
   
   add_option('last_execute',(time()-3600));

   $table_name = $wpdb->prefix . "smtp_servers";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	$sql = "CREATE TABLE " . $table_name . " (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT, 
	`server_address` VARCHAR(255) NOT NULL, 
	`port` VARCHAR(255) NOT NULL, 
	`username` VARCHAR(255) NOT NULL, 
	`password` VARCHAR(255) NOT NULL, 
	`num_times_used` INT NOT NULL DEFAULT '0', 
	`date_last_used` VARCHAR(50) NULL,
	`created_at` VARCHAR(50) NULL,
	`active` VARCHAR(1) NOT NULL, 
	PRIMARY KEY (`id`)) ENGINE = MyISAM";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	//add_option("be_video_db_version", $vdo_db_version);
   }
   
   
   $table_name = $wpdb->prefix . "smtp_message";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	$sql = "CREATE TABLE " . $table_name . " (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT, 
	`title` VARCHAR(255) NOT NULL, 
	`subject` VARCHAR(255) NOT NULL, 
	`body` TEXT NOT NULL,
	`spin_txt` VARCHAR(1) NULL, 
	`created_at` VARCHAR(50) NULL,
	`active` VARCHAR(1) NOT NULL, 
	PRIMARY KEY (`id`)) ENGINE = MyISAM";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
   }
   
   $table_name = $wpdb->prefix . "messages_queue";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	$sql = "CREATE TABLE " . $table_name . " (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT, 
	`mid` BIGINT(20) NOT NULL,
	`to` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255) NOT NULL, 
	`subject` VARCHAR(255) NOT NULL, 
	`body` TEXT NOT NULL,
	`status` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`id`)) ENGINE = MyISAM";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
   }
   
   /*$table_name = $wpdb->prefix . "smtp_last_time";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	$sql = "CREATE TABLE " . $table_name . " (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT, 
	`last_exec` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`id`)) ENGINE = MyISAM";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
   }*/
   
   
}

function smtp_cp_db_uninstall() {
	global $wpdb;
	$table_name = $wpdb->prefix . "smtp_servers";
	$table_name2 = $wpdb->prefix . "messages_queue";
	$table_name3 = $wpdb->prefix . "smtp_message";
	//$table_name4 = $wpdb->prefix . "smtp_last_time";
	$dsql = "DROP TABLE ".$table_name;
	$dsql2 = "DROP TABLE ".$table_name2;
	$dsql3 = "DROP TABLE ".$table_name3;
	//$dsql4 = "DROP TABLE ".$table_name4;
	$wpdb->query($dsql);
	$wpdb->query($dsql2);
	$wpdb->query($dsql3);
	//$wpdb->query($dsql4);
	
	delete_option('last_execute');
}

register_activation_hook(__FILE__,'smtp_cp_db_install');

register_deactivation_hook(__FILE__,'smtp_cp_db_uninstall');

add_action('admin_menu', 'smtp_cp_add_pages');

function smtp_cp_add_pages() {
	add_menu_page('SMTP Cycle', 'SMTP Server', 'manage_options', 'smtp_cp_server_page', 'smtp_cp_server' );
	
	add_submenu_page('smtp_cp_server_page', 'View/Add Messages', 'View/Add Messages', 'manage_options', 'smtp_cp_view_add_msg_page', 'smtp_cp_view_add_msg');
	
	add_submenu_page('smtp_cp_server_page', 'Schedule Message', 'Schedule Message', 'manage_options', 'smtp_cp_schedule_message_page', 'smtp_cp_schedule_message');
	
	add_submenu_page('smtp_cp_server_page', 'Processed Messages', 'Processed Messages', 'manage_options', 'smtp_cp_processed_messages_page', 'smtp_cp_processed_messages');
	
	add_submenu_page('smtp_cp_server_page', 'Options', 'Options', 'manage_options', 'smtp_cp_options_page', 'smtp_cp_options');

	add_action( 'admin_init', 'register_smtp_ce_settings' );
}

if (isset($_POST['msg_queue_submit'])){
if ($_POST['msg_queue_submit'] == 'yes'){

	global $wpdb;
	$table_name = $wpdb->prefix . "messages_queue";
	$table_msg = $wpdb->prefix . "smtp_message";
	$msg = "";

	$sql = "SELECT * FROM ".$table_msg." WHERE id=".$_POST['mid']." LIMIT 0,1";
	$msginfo = $wpdb->get_row($sql);
	
	

	$arr = explode(',',$_POST['to']);
	//print_r($arr);
	$c=0;
	foreach($arr as $val){
		$email = $val;
		$regexp = "/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";
		
		if (preg_match($regexp, $email)) {
			if ($msginfo->spin_txt == '1') {
				$email_sub = smtp_cp_spin($msginfo->subject);
				$email_body = smtp_cp_spin($msginfo->body);
			} else {
				$email_sub = $msginfo->subject;
				$email_body = $msginfo->body;
			}
			$res = $wpdb->insert( $table_name, array(
			'mid' => $_POST['mid'],
			'to' => $val,
			'title' => $msginfo->title,
			'subject' => $email_sub,
			'body' => $email_body,
			'status' => 'scheduled',
			));
		} else {
			$msg .= "$email email is invalid!<br />";
			break;
		}	
		
		if($res) $c++;
	}
	
	if($c>0) {
		$msg .= "$c Record inserted successfully!!!<br />";
	} else {
		$msg .= "Insert fail!<br />";
	}
	
}
}

if (isset($_POST['server_address_submit'])){
if ($_POST['server_address_submit'] == 'yes'){
	
	global $wpdb;
	$table_servers = $wpdb->prefix . "smtp_servers";
	
	if (isset($_POST['mode'])){
	if ($_POST['mode'] == 'addnew'){
		//print_r($_POST);
		$msg = "";
		$table_servers = $wpdb->prefix . "smtp_servers";
		$res = $wpdb->insert( $table_servers, array( 
		'server_address' => htmlentities($_POST['server_address']),
		'port' => htmlentities($_POST['port']),
		'username'  => htmlentities($_POST['username']), 
		'password'=>htmlentities($_POST['password']), 
		'active'=>$_POST['active'], 
		'created_at'=> time() 
		));
		
		if($res) {
			$msg = "Insert successfully!!!";
		} else {
			$msg = "Insert fail!";
		}
	}}
	if (isset($_POST['mode'])){
	if ($_POST['mode'] == 'edit'){
		$res = $wpdb->update( $table_servers, array(
		'server_address' => htmlentities($_POST['server_address']),
		'port' => htmlentities($_POST['port']), 
		'username'  => htmlentities($_POST['username']), 
		'password'=> htmlentities($_POST['password']), 
		'active'=>$_POST['active'], 
		), array( 'id' => $_POST['id'] ) );
		
		if($res) {
			$msg = "Update successfully!!!";
		} else {
			$msg = "Update fail!";
		}
	}}
}
}

if (isset($_POST['add_msg_submit'])){
if ($_POST['add_msg_submit'] == 'yes'){
	
	global $wpdb;
	$table_name = $wpdb->prefix . "smtp_message";
	if ($_POST['mode'] == 'addnew'){
		//print_r($_POST);
		$msg = "";
		$res = $wpdb->insert( $table_name, array( 
		'title' => htmlentities($_POST['title']),
		'subject' => htmlentities($_POST['subject']),
		'body'  => htmlentities($_POST['body']),
		'spin_txt'=>$_POST['spin_txt'],
		'created_at'=> time()
		));
		
		if($res) {
			$msg = "Insert successfully!!!";
		} else {
			$msg = "Insert fail!";
		}
	}
	if ($_POST['mode'] == 'edit'){
		$res = $wpdb->update( $table_name, array(
		'title' => htmlentities($_POST['title']),
		'subject' => htmlentities($_POST['subject']), 
		'body'  => htmlentities($_POST['body']), 
		'spin_txt'=>$_POST['spin_txt'],
		), array( 'id' => $_POST['id'] ) );
		
		if($res) {
			$msg = "Update successfully!!!";
		} else {
			$msg = "Update fail!";
		}
	}
}
}

function register_smtp_ce_settings() {
	register_setting( 'smtp_ce-settings-group', 'smtp_ce-recurrence' );
}

function smtp_cp_options() {
?>
<div class="wrap">
<h2>SMTP Cycle Email Options</h2>
<form method="post" action="options.php">
    <?php settings_fields( 'smtp_ce-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Recurrence</th>
		<?php 
		$r = get_option('smtp_ce-recurrence'); 
		?>
        <td><select style="width:120px;" name="smtp_ce-recurrence" id="smtp_ce-recurrence">
			<option <?php if($r=='hourly')echo 'selected="selected"' ?> value="hourly">Hourly</option>
			<option <?php if($r=='twicedaily')echo 'selected="selected"' ?> value="twicedaily">Twice Daily</option>
			<option <?php if($r=='daily')echo 'selected="selected"' ?> value="daily">Daily</option>
		</select>&nbsp;<em>How often the event should reoccur</em></td>
        </tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php 
}


function smtp_cp_server() {
global $wpdb;
$table_servers = $wpdb->prefix . "smtp_servers";

$server_address = '';
$port = '';
$username = '';
$password = '';
$active = '';

if (isset($_GET['mode'])){
if ($_GET['mode']=='edit' and $_GET['id']!='' ){
	$sql = "SELECT * FROM ".$table_servers." WHERE id=".$_GET['id']." LIMIT 0,1";
	$vdoinfo = $wpdb->get_row($sql);
	
	$server_address = $vdoinfo->server_address;
	$port = $vdoinfo->port;
	$username = $vdoinfo->username;
	$password = $vdoinfo->password;
	$active = $vdoinfo->active;
	
}}else{
	$sql = "SELECT * FROM ".$table_servers." WHERE 1 ORDER BY id ASC";
	$video_info = $wpdb->get_results($sql);
}

//$video_info
//print_r($video_info);
?>
<script type="text/javascript">
function show_confirm(title, id)
{
	var rpath1 = "";
	var rpath2 = "";
	var r=confirm('Are you confirm to delete ['+title+']');
	if (r==true)
	{
		rpath1 = '<?php echo $_SERVER['REQUEST_URI']; ?>';
		rpath2 = '&delete=y&table=smtp_servers&id='+id;
		//alert(rpath1+rpath2);
		window.location = rpath1+rpath2;
	}
}
</script>

<div class="wrap">
	<h2><?php echo (isset($_GET['mode']) ? 'Edit ' : 'Add '); ?>SMTP Server</h2>
	<?php 
	global $msg;
	if ($msg != '') echo '<div id="message" class="updated fade">'.$msg.'</div>';
	?>
<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <table class="form-table">
        <tr valign="top">
			<th scope="row">Server Address</th>
			<td><input type="text" name="server_address" id="server_address" class="regular-text" value="<?php echo $server_address ?>" /></td>
        </tr>
        <tr valign="top">
			<th scope="row">Port</th>
			<td><input type="text" name="port" id="port" class="regular-text" value="<?php echo $port ?>" /></td>
        </tr>
        <tr valign="top">
			<th scope="row">Username</th>
			<td><input type="text" name="username" id="username" class="regular-text" value="<?php echo $username;?>" /></td>
        </tr>
        <tr valign="top">
			<th scope="row">Password</th>
			<td><input type="text" name="password" id="password" class="regular-text" value="<?php echo $password;?>" /></td>
        </tr>
        <tr valign="top">
			<th scope="row">Active</th>
			<td><select name="active" id="active" class="regular-text">
			<option <?php if($active == '0')echo 'selected="selected"';?> value="0">Inactive</option>
			<option <?php if($active == '1')echo 'selected="selected"';?> value="1">Active</option>
			</select></td>
        </tr>
	</table>
	<input type="hidden" name="server_address_submit" id="server_address_submit" value="yes" />
	<p class="submit">
    <input type="submit" id="submit_button" name="submit_button" class="button-primary" value="<?php _e('Save Changes') ?>" />
	<?php if( isset($_GET['mode'] )) { if ($_GET['mode']=='edit' and $_GET['id']!='' ) {?>
	<input type="hidden" name="mode" id="mode" value="edit" />
	<input type="hidden" name="id" id="id" value="<?php echo $_GET['id']; ?>" />
	<a style="text-decoration:none;" href="?page=smtp_cp_server_page"><input type="button" class="button" value="<?php _e('Cancel') ?>" /></a>
	<?php } } else { ?>
	<input type="hidden" name="mode" id="mode" value="addnew" />
	<?php } ?>
    </p>	
</div>

<?php if ( !isset($_GET['mode']) ) { ?>
<div class="wrap">
<table class="widefat page fixed" cellspacing="0">
	<thead>
	<tr valign="top">
		<th class="manage-column column-title" scope="col" width="100">Server</th>
		<th class="manage-column column-title" scope="col" width="50">Port</th>
		<th class="manage-column column-title" scope="col" width="100">Username</th>
		<th class="manage-column column-title" scope="col" width="100">Password</th>
		<th class="manage-column column-title" scope="col" width="100">#Times Used</th>
		<th class="manage-column column-title" scope="col" width="100">Last Used</th>
		<th class="manage-column column-title" scope="col" width="50">Active</th>
		<th class="manage-column column-title" scope="col" width="50">Edit</th>
		<th class="manage-column column-title" scope="col" width="50">Delete</th>
	</tr>
	</thead>
	
	<tbody>
	<?php foreach($video_info as $vdoinfo){ ?>
	<tr valign="top">
		<td>
			<?php echo $vdoinfo->server_address;?>
		</td>
		<td>
			<?php echo $vdoinfo->port;?>
		</td>
		<td>
			<?php echo $vdoinfo->username;?>
		</td>
		<td>
			<?php echo $vdoinfo->password;?>
		</td>
		<td>
			<?php echo $vdoinfo->num_times_used;?>
		</td>
		<td>
			<?php echo $vdoinfo->date_last_used;?>
		</td>
		<td>
			<?php echo ($vdoinfo->active == 1 ? 'Active' : 'Inactive');?>
		</td>
		<td>
			<a href="?page=smtp_cp_server_page&mode=edit&id=<?php echo $vdoinfo->id;?>"><strong>Edit</strong></a>
		</td>
		<td>
			<a onclick="show_confirm('<?php echo 'Server:'.$vdoinfo->server_address.' Port:'.$vdoinfo->port?>','<?php echo $vdoinfo->id;?>');" href="#delete"><strong>Delete</strong></a>
		</td>
	</tr>
	<?php }?>
	</tbody>
	<tfoot>
	<tr valign="top">
		<th class="manage-column column-title" scope="col" width="100">Server</th>
		<th class="manage-column column-title" scope="col" width="50">Port</th>
		<th class="manage-column column-title" scope="col" width="100">Username</th>
		<th class="manage-column column-title" scope="col" width="100">Password</th>
		<th class="manage-column column-title" scope="col" width="100">#Times Used</th>
		<th class="manage-column column-title" scope="col" width="100">Last Used</th>
		<th class="manage-column column-title" scope="col" width="50">Active</th>
		<th class="manage-column column-title" scope="col" width="50">Edit</th>
		<th class="manage-column column-title" scope="col" width="50">Delete</th>
	</tr>
	</tfoot>
</table>
</div>
<?php
	}
}

function smtp_cp_view_add_msg() {

global $wpdb;
$table_msg = $wpdb->prefix . "smtp_message";

$title = '';
$subject = '';
$body ='';

if(isset($_GET['id'])){
if ($_GET['mode']=='edit' and $_GET['id']!='' ){
	$sql = "SELECT * FROM ".$table_msg." WHERE id=".$_GET['id']." LIMIT 0,1";
	$vdoinfo = $wpdb->get_row($sql);
	
	$title = $vdoinfo->title;
	$subject = $vdoinfo->subject;
	$body = $vdoinfo->body;
	
}}else{
	$sql = "SELECT * FROM ".$table_msg." WHERE 1 ORDER BY id ASC";
	$video_info = $wpdb->get_results($sql);
}
?>
<div class="wrap">
	<h2><?php echo (isset($_GET['mode']) ? 'Edit ' : 'Add '); ?>Messages</h2>
	<?php 
	global $msg;
	if ($msg != '') echo '<div id="message" class="updated fade">'.$msg.'</div>';
	?>
<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <table class="form-table">
        <tr valign="top">
			<th scope="row">Title</th>
			<td><input type="text" name="title" id="title" class="regular-text" value="<?php echo $title;?>" /></td>
        </tr>
        <tr valign="top">
			<th scope="row">Subject</th>
			<td><input type="text" name="subject" id="subject" class="regular-text" value="<?php echo $subject;?>" /></td>
        </tr>
        <tr valign="top">
			<th scope="row">Body</th>
			<td><textarea rows="10" cols="50" name="body" id="body" class="large-text"><?php echo $body;?></textarea><br /><em>Sample spin txet <code>{This|Here} is {some|a {little|wee} bit of} {example|sample} text</code></em></td>
        </tr>
        <tr valign="top">
			<th scope="row">Spin Text</th>
			<?php if (isset($_GET['mode'])) { if ($_GET['mode']=='edit' ) { ?>
			<td><input type="checkbox" name="spin_txt" id="spin_txt" <?php echo ($vdoinfo->spin_txt=='1' ? 'checked="checked"' : '' )  ?> value="1" /></td>
			<?php } } else { ?>
			<td><input type="checkbox" name="spin_txt" id="spin_txt" checked="checked" value="1" /></td>
			<?php } ?>
        </tr>
	</table>
	<input type="hidden" name="add_msg_submit" id="add_msg_submit" value="yes" />
	<p class="submit">
    <input type="submit" id="submit_button" name="submit_button" class="button-primary" value="<?php _e('Save Changes') ?>" />
	<?php if (isset($_GET['mode'])) { if ($_GET['mode']=='edit' and $_GET['id']!='' ) {?>
	<input type="hidden" name="mode" id="mode" value="edit" />
	<input type="hidden" name="id" id="id" value="<?php echo $_GET['id']; ?>" />
	<a style="text-decoration:none;" href="?page=smtp_cp_view_add_msg_page"><input type="button" class="button" value="<?php _e('Cancel') ?>" /></a>
	<?php } } else { ?>
	<input type="hidden" name="mode" id="mode" value="addnew" />
	<?php } ?>
    </p>	
</div>	

<?php if ( !isset($_GET['mode']) ) { ?>
<script type="text/javascript">
function show_confirm(title, id)
{
	var rpath1 = "";
	var rpath2 = "";
	var r=confirm('Are you confirm to delete ['+title+']');
	if (r==true)
	{
		rpath1 = '<?php echo $_SERVER['REQUEST_URI']; ?>';
		rpath2 = '&delete=y&table=smtp_message&id='+id;
		//alert(rpath1+rpath2);
		window.location = rpath1+rpath2;
	}
}
</script>
<div class="wrap">
<table class="widefat page fixed" cellspacing="0">
	<thead>
	<tr valign="top">
		<th class="manage-column column-title" scope="col" width="100">Title</th>
		<th class="manage-column column-title" scope="col" width="100">Subject</th>
		<th class="manage-column column-title" scope="col" width="400">Body</th>
		<th class="manage-column column-title" scope="col" width="40">Spin Text</th>
		<th class="manage-column column-title" scope="col" width="50">Edit</th>
		<th class="manage-column column-title" scope="col" width="50">Delete</th>
	</tr>
	</thead>
	
	<tbody>
	<?php foreach($video_info as $vdoinfo){ ?>
	<tr valign="top">
		<td>
			<?php echo $vdoinfo->title;?>
		</td>
		<td>
			<?php echo $vdoinfo->subject;?>
		</td>
		<td>
			<?php echo $vdoinfo->body;?>
		</td>
		<td>
			<input type="checkbox" <?php echo ($vdoinfo->spin_txt=='1' ? 'checked="checked"' : '' )  ?>  />
		</td>		
		<td>
			<a href="?page=smtp_cp_view_add_msg_page&mode=edit&id=<?php echo $vdoinfo->id;?>"><strong>Edit</strong></a>
		</td>
		<td>
			<a onclick="show_confirm('<?php echo $vdoinfo->title;?>','<?php echo $vdoinfo->id;?>');" href="#delete"><strong>Delete</strong></a>
		</td>
	</tr>
	<?php }?>
	</tbody>
	<tfoot>
	<tr valign="top">
		<th class="manage-column column-title" scope="col" width="100">Title</th>
		<th class="manage-column column-title" scope="col" width="100">Subject</th>
		<th class="manage-column column-title" scope="col" width="400">Body</th>
		<th class="manage-column column-title" scope="col" width="40">Spin Text</th>
		<th class="manage-column column-title" scope="col" width="50">Edit</th>
		<th class="manage-column column-title" scope="col" width="50">Delete</th>
	</tr>
	</tfoot>
</table>
</div>
<?php
	}
}

function smtp_cp_schedule_message() {
global $wpdb;
$table_msg = $wpdb->prefix . "smtp_message";

$sql = "SELECT * FROM ".$table_msg." WHERE 1 ORDER BY id ASC";
$video_info = $wpdb->get_results($sql);
?>
<div class="wrap">
<?php 
	global $msg;
	if ($msg != '') echo '<div id="message" class="updated fade">'.$msg.'</div>';
	?>
	<h2>Schedule Message</h2>
	
<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <table class="form-table">
        <tr valign="top">
			<th scope="row">TO</th>
			<td><input type="text" name="to" id="to" class="large-text" value="" /><br /><em>Multiple email address comma separated</em></td>
        </tr>
        <tr valign="top">
			<th scope="row">Messages</th>
			<td><select id="mid" name="mid">
			<?php foreach($video_info as $vdoinfo){ ?>
			<option value="<?php echo $vdoinfo->id; ?>"><?php echo $vdoinfo->title; ?></option>
			<?php } ?>
			</select></td>
        </tr>
	</table>
	<input type="hidden" name="msg_queue_submit" id="msg_queue_submit" value="yes" />
	<p class="submit"><input type="submit" id="submit_button" name="submit_button" class="button-primary" value="<?php _e('Submit') ?>" /></p>
		
</form>
</div>
<?php 
}

function smtp_ce_send_email() {

	global $wpdb, $msg;

	$ltime = get_option('last_execute');
	$ctime = time();
	
	if(($ctime - $ltime) > 60) {
		
		$table_msgq = $wpdb->prefix . "messages_queue";
		$sql = "SELECT * FROM ".$table_msgq." WHERE status='scheduled' ORDER BY id LIMIT 0,1";
		//echo $sql; 
		$msg_info = $wpdb->get_row($sql);
		
		//print_r($msg_info);
		$emailID = $msg_info->id;
		$to = $msg_info->to;
		$subject = $msg_info->subject;
		$body = $msg_info->body;
		
		//echo $subject.$body;
		
		$table_srvr = $wpdb->prefix . "smtp_servers";
		$sql = "SELECT * FROM ".$table_srvr." WHERE active='1' ORDER BY date_last_used ASC LIMIT 0,1";
		//echo $sql; 
		
		$srvr_info = $wpdb->get_row($sql);
		
		$svrID = $srvr_info->id; 
		$host = $srvr_info->server_address;
		$port = $srvr_info->port;
		$username = $srvr_info->username;
		$password = $srvr_info->password;
		
		//echo $username.$password;
		
		function smtp_cp_phpmailer_init($h,$p,$u,$p) {
			global $subject, $to, $body, $svrID, $wpdb, $table_srvr, $emailID, $table_msgq;
			global $wpdb;
			
			if(isset($phpmailer)) {
				if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
					require_once ABSPATH . WPINC . '/class-phpmailer.php';
					require_once ABSPATH . WPINC . '/class-smtp.php';
					$phpmailer = new PHPMailer();
					
					$phpmailer->Mailer = 'smtp';
					$phpmailer->Sender = $phpmailer->From;
					$phpmailer->SMTPSecure = '';
					$phpmailer->Host = $h;
					$phpmailer->Port = $p;
					$phpmailer->SMTPAuth = true;
					$phpmailer->Username = $u;
					$phpmailer->Password = $p;
					
					$result = wp_mail($to,$subject,$body);
					unset($phpmailer);
					
					
					if ($result){
						$res = $wpdb->update( $table_srvr, array(
						'date_last_used' => time(),
						), array( 'id' => $svrID ) );
						
						$res = $wpdb->update( $table_msgq, array(
						'status' => 'sent',
						), array( 'id' => $emailID ) );
					} else {
						$res = $wpdb->update( $table_srvr, array(
						'active' => '0',
						), array( 'id' => $svrID ) );
					}
				}
			}
		
		}
		
		smtp_cp_phpmailer_init($host, $port, $username, $password);
		update_option( 'last_execute', time() );
		
	}
	else
	{
		$msg = 'Time not expired yet.';
	}
	
}

if( isset ($_POST['send_email']) ) {
	
	add_action('plugins_loaded', 'smtp_ce_send_email');
	do_action('smtp_ce_send_email');
}

function smtp_cp_processed_messages() {
global $wpdb;
$table_msgq = $wpdb->prefix . "messages_queue";

$where = ' 1 ';
if( isset ($_POST['email_filter']) and isset ($_POST['status']) ){
if ($_POST['email_filter'] == 'Filter' and $_POST['status']!= '')
{
	$where = " status='".$_POST['status']."'";
}} else {
	$where = ' 1 ';
}

$sql = "SELECT * FROM ".$table_msgq." WHERE $where LIMIT 0,50";
//echo $sql; 
$msg_info = $wpdb->get_results($sql);
?>
<div class="wrap">
	<?php 
	global $msg;
	if ($msg != '') echo '<div id="message" class="updated fade">'.$msg.'</div>';
	?>

<h2>Processed Message</h2>

<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<label>Filter by email status </label><select name="status">
<?php
$stat = isset($_POST['status'])?$_POST['status']:'';
?>
<option <?php if($stat == '') echo 'selected="selected"'; ?> value="">all</option>
<option <?php if($stat == 'sent') echo 'selected="selected"'; ?> value="sent">sent</option>
<option <?php if($stat == 'scheduled') echo 'selected="selected"'; ?> value="scheduled">scheduled</option>
<option <?php if($stat == 'pause') echo 'selected="selected"'; ?> value="pause">pause</option>
</select>
<input class="button" name="email_filter" type="submit" value="Filter" />
</form>
<table class="widefat page fixed" cellspacing="0">
	<thead>
	<tr valign="top">
		<th class="manage-column column-title" scope="col" width="100">Title</th>
		<th class="manage-column column-title" scope="col" width="100">Subject</th>
		<th class="manage-column column-title" scope="col" width="400">Body</th>
		<th class="manage-column column-title" scope="col" width="50">Status</th>
	</tr>
	</thead>
	
	<tbody>
	<?php foreach($msg_info as $vdoinfo){ ?>
	<tr valign="top">
		<td>
			<?php echo $vdoinfo->title;?>
		</td>
		<td>
			<?php echo $vdoinfo->subject;?>
		</td>
		<td>
			<?php echo $vdoinfo->body;?>
		</td>
		<td>
			<?php echo ucfirst($vdoinfo->status);?>
		</td>		
	</tr>
	<?php }?>
	</tbody>
	<tfoot>
	<tr valign="top">
		<th class="manage-column column-title" scope="col" width="100">Title</th>
		<th class="manage-column column-title" scope="col" width="100">Subject</th>
		<th class="manage-column column-title" scope="col" width="400">Body</th>
		<th class="manage-column column-title" scope="col" width="50">Status</th>
	</tr>
	</tfoot>
</table>
</div>
<form name="email_send" method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<p class="submit"><input type="submit" id="send_email" name="send_email" class="button-primary" value="<?php _e('Send Email Manually') ?>" /></p>
</form>
<?php 
}

?>