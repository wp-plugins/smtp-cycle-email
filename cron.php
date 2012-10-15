<?php
include_once('../../../wp-load.php');
$ltime = get_option('last_execute');
$ctime = time();

if(($ctime - $ltime) > 300) {
	
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
	
	//echo $username.$password;
	
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
	
	}
	
	smtp_cp_phpmailer_init($host, $port, $username, $password);
	update_option( 'last_execute', time() );
	
}