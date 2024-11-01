<?php
/*
  Plugin Name: Web3 Login
  Plugin URI: https://airdropmytoken.com/
  Description: Login & Sign-up with Web3 - Torus, Metamask, Walletconnect
  Version: 0.1.0
  Author: airDropMyToken
  Author URI: https://twitter.com/airDropMyToken
  License: GPLv2 or later
 */


defined( 'ABSPATH' ) || die;


load_plugin_textdomain( 'Web3Login', false, basename( dirname( __FILE__ ) ) . '/languages' );
function sc_f_web3login($attr){
	$option_css="#how_to_signup{display:none;}";
	if(is_array($attr)){
	}
	$site_url=get_site_url();
	$css_href=includes_url()."css/buttons.min.css?ver=$wp_version";
	$html=<<<EOM
		<link rel="stylesheet" type="text/css" href="$css_href" />
		<form name="loginform" id="loginform" action="$site_url/wp-login.php" method="post"></form>
EOM;
	$html.=Web3Login_login_head();
	$html.=Web3Login_login_form();
	$html.=Web3Login_login_footer();
	Web3Login_add_scripts();
	$html="<div class='wp-core-ui'>$html</div><style>\n$option_css\n</style>";
	return $html;
}
add_shortcode( 'web3login', 'sc_f_web3login' );
//add_shortcode( 'web3login', array( $this, 'sc_f_web3login' ));


load_plugin_textdomain( 'Web3Login', false, basename( dirname( __FILE__ ) ) . '/languages' );

function Web3Login_install_check(){
	global $wpdb, $current_user;
	$reload="";
	$failed="";
	if(wp_verify_nonce($_POST['_wpnonce'],Web3Login::CREDENTIAL_NAME)){
		if($_POST['step']=="which_python3"){
			$cmd="which python{,3}";
			//$cmd="which python3";
			$out=null; $ret=null;			
			if(exec($cmd,$out,$ret)){
				//$str="no python3 support";
				//$str="";
				foreach($out as $python_path){
					if(preg_match("/\/python/",$python_path)){
						$out0=null; $ret=null;
						$result_exec=exec("$python_path  -m pip install web3 --user ",$out0,$ret);
						//$str.=implode("\n",$out0);
						$str.="$result_exec\n"; //Successfully installed aiohttp-3.8.1 aiosignal-1.2.0 async-timeout-4.0.2 asynctest-0.13.0 attrs-21.4.0 base58-2.1.1 bitarray-1.2.2 certifi-2021.10.8 charset-normalizer-2.0.11 cytoolz-0.11.2 eth-abi-2.1.1 eth-account-0.5.7 eth-keyfile-0.5.1 eth-keys-0.3.4 eth-rlp-0.2.1 eth-utils-1.10.0 frozenlist-1.2.0 hexbytes-0.2.2 idna-3.3 idna-ssl-1.1.0 importlib-metadata-4.8.3 importlib-resources-5.4.0 ipfshttpclient-0.8.0a2 jsonschema-3.2.0 lru-dict-1.1.7 multiaddr-0.0.9 multidict-5.2.0 netaddr-0.8.0 parsimonious-0.8.1 protobuf-3.19.4 pycryptodome-3.14.1 pyrsistent-0.18.0 requests-2.27.1 rlp-2.0.1 typing-extensions-4.0.1 urllib3-1.26.8 varint-1.0.2 web3-5.27.0 websockets-9.1 yarl-1.7.2

						if(preg_match("/error/",$result_exec)){
							$str.="failed installation by $python_path ..\n";
							$failed="failed";
						//}elseif(count($out0)>10){
						}elseif(preg_match("/Successfully installed/",$result_exec) || preg_match("/already satisfied/",$result_exec)){
							$out0=null; $ret=null;
							$result_exec=exec("$python_path  -m pip list | grep web3",$out0,$ret);
							//$str.=implode("\n",$out0);
							if(preg_match("/\d\.\d/",$result_exec)){
								//$str.="$result_exec\n";
								//$str.="successfully installation by $python_path ..\n";	
								if(!is_dir(__DIR__."/.python3app")){
									mkdir(__DIR__."/.python3app");
									copy(__DIR__."/.web3scripts/ecrecover.py",__DIR__."/.python3app/ecrecover.py");
									$str="Successfully installed with $python_path ! \n";
									update_option(Web3Login::PLUGIN_DB_PREFIX.'ecrecover_cmd',"$python_path ".__DIR__."/.python3app/ecrecover.py");
									$redirect_url=get_edit_user_link($current_user->ID);
									if ( preg_match( '/^http(s)?:\/\/[^\/\s]+(.*)$/', $redirect_url, $match ) ) {
										$redirect_url = $match[2];
									}
									update_option(Web3Login::PLUGIN_DB_PREFIX.'redirect_url',$redirect_url);
									$reload="reload";
								}
								
							}else{
								$str.="failed installation by $python_path , please check permission to write ~/.local  ..\n";
								$failed="failed";

							}
						}else{
							$failed="failed";
							$str.="failed installation by $python_path , please check permission to write ~/.local  ..\n";
						}
					}
					
				}
				$out_arr=array("success"=>$str);
				if($reload!=""){$out_arr["reload"]=$reload;}
				elseif($failed!=""){$out_arr["failed"]=$failed;}
				echo json_encode($out_arr);
			}else{
				echo json_encode(array("success"=>"no python"));
			}
		}elseif($_POST['step']=="which_npm"){
			$cmd="node --version";
			$out=null; $ret=null;
			if(preg_match("/\d\.\d/",exec($cmd,$out,$ret))){
				$cmd="npm --version";
				$out=null; $ret=null;
				if(preg_match("/\d\.\d/",exec($cmd,$out,$ret))){
					if(!is_dir(__DIR__."/.nodeapp")){mkdir(__DIR__."/.nodeapp"); copy(__DIR__."/.web3scripts/ecrecover.js",__DIR__."/.nodeapp/ecrecover.js");}

					$cmd="npm install ethers ethers-utils --prefix=".__DIR__."/.nodeapp";
					$str.=$cmd."\n";
					$out=null; $ret=null;
					$result_exec=exec($cmd,$out,$ret);
					$out_str=implode("\n",$out);
					$str.=$out_str;
					if(preg_match("/\+-- ethers\@\d/",$out_str)){
						copy(__DIR__."/.web3scripts/node_modules/ws/index.js",__DIR__."/.nodeapp/node_modules/ws/index.js");
						$str.="cp ".__DIR__."/.web3scripts/node_modules/ws/index.js ".__DIR__."/.nodeapp/node_modules/ws/index.js";
						update_option(Web3Login::PLUGIN_DB_PREFIX.'ecrecover_cmd',"node ".__DIR__."/.nodeapp/ecrecover.js");

						$redirect_url=get_edit_user_link($current_user->ID);
						if ( preg_match( '/^http(s)?:\/\/[^\/\s]+(.*)$/', $redirect_url, $match ) ) {
							$redirect_url = $match[2];
						}
						update_option(Web3Login::PLUGIN_DB_PREFIX.'redirect_url',$redirect_url);
						$reload="reload";

					}else{
						$str.="failed installing with nodejs..\n";
						$failed="failed";

					}

				}else{
					$str="no support node/npm";
					update_option('Web3Login_support_node',"failed");
					$failed="failed";
				}
			}else{
				$str="no support node/npm";
				update_option('Web3Login_support_node',"failed");
				$failed="failed";
			}

			$out_arr=array("success"=>$str);
			if($reload!=""){$out_arr["reload"]=$reload;}
			elseif($failed!=""){$out_arr["failed"]=$failed;}
			echo json_encode($out_arr);


		}else{
			echo json_encode(array("success"=>"system not supported"));

		}
	}else{
		echo json_encode(array("success"=>__("nonce failed, expired. please reload this page.","Web3Login")));
	}
	die();
}
function Web3Login_sig_check(){
	global $wpdb, $current_user;
	$redirect_url = get_option(Web3Login::PLUGIN_DB_PREFIX.'redirect_url');

	if(wp_verify_nonce($_POST['_wpnonce'],Web3Login::CREDENTIAL_NAME)){
		$message=Web3Login_get_message($_POST['_wpnonce']);
		if($current_user->ID>0){
			echo json_encode(array("url"=>$redirect_url));
		}else{
			$ecrecover_cmd = get_option(Web3Login::PLUGIN_DB_PREFIX.'ecrecover_cmd');
			$signature=preg_replace("/[^0-9a-zA-Z]/","",$_POST['signature']);


			$addr="failed loading extension";
			if(preg_match("/ecrecover\.php/",$ecrecover_cmd)){
				if(extension_loaded( 'gmp' ) || extension_loaded( 'bcmath' )){
					require_once("elliptic-php/ecrecover.php");

					$addr= ecrecover($message, $signature);
					$addr=preg_replace("/\n/","",$addr);
				}else{

				}

			}else{
				$cmd="$ecrecover_cmd '$message' '{$signature}'";
				$arr=null; $ret=null;
				$addr=exec($cmd,$arr,$ret);
				//$insert_id=Web3Login_query_prepare("INSERT INTO `app_meta` (`key`,`val`) VALUES ('%s','%s') ",["debug:{$wpdb->prefix}",json_encode(array("cmd"=>"$cmd","addr"=>$addr,"_POST"=>$_POST))]);

			}

			$address=preg_replace("/[^0-9a-zA-Z]/","",$_POST['address']);
			$address_lower=strtolower($address);

			if(strtolower($addr)==$address_lower){
				$results=Web3Login_get_results_prepare("SELECT * FROM `{$wpdb->prefix}usermeta` WHERE `user_id` > 0 AND `meta_key` = 'web3_address' AND `meta_value` = '%s' ",[$address_lower]);
				if(count($results)>0){
					$user_id=0;
					foreach($results as $row){
						$user_id=$row['user_id'];
					}
					$user_data=get_userdata($user_id);
					wp_set_current_user($user_id,$user_data->user_login);
					wp_set_auth_cookie($user_id);
					do_action('wp_signon', $user_data->user_login);
					echo json_encode(array("url"=>$redirect_url));		

				}else{
		
					$user_name=Web3Login_generateRandomString(16);
					while ( username_exists( $user_name ) ) {
						$user_name=Web3Login_generateRandomString(16);
					}
					$user_pass=Web3Login_generateRandomString(16);
					$user_email="WP-Web3-user-{$user_name}@{$_SERVER['SERVER_NAME']}";
					wp_create_user($user_name,$user_pass,$user_email);
					$user_id=username_exists( $user_name );
					wp_set_current_user($user_id,$user_name);
					wp_set_auth_cookie($user_id);
					do_action('wp_signon', $user_name);
					update_user_meta($user_id,"web3_address",$address_lower);
					wp_update_user(array('ID'=>$user_id,'display_name'=>$address_lower));
					echo json_encode(array("url"=>$redirect_url));		
				}
			}else{
				http_response_code( 400 ) ;
				echo json_encode(array("err"=>__("signiture failed","Web3Login")));
			}
		}
	}else{
		http_response_code( 400 ) ;
		echo json_encode(array("err"=>__("nonce failed, expired. please reload this page.","Web3Login")));
	}
	die();
}
add_action( 'wp_ajax_Web3Login_sig_check', 'Web3Login_sig_check' );
add_action( 'wp_ajax_nopriv_Web3Login_sig_check', 'Web3Login_sig_check' );

add_action( 'wp_ajax_Web3Login_install_check', 'Web3Login_install_check' );
add_action( 'wp_ajax_nopriv_Web3Login_install_check', 'Web3Login_install_check' );


add_action( 'login_footer', 'Web3Login_login_footer_echo' );
add_action( 'login_head', 'Web3Login_login_head_echo' );
add_action( 'login_form', 'Web3Login_login_form_echo' );

function Web3Login_login_head_echo(){
	echo Web3Login_login_head();
}
function Web3Login_login_footer_echo(){
	echo Web3Login_login_footer();
}
function Web3Login_login_form_echo(){
	echo Web3Login_login_form();
}

add_filter( 'nonce_life', function () {
	// 1時間
	return MINUTE_IN_SECONDS*5;
});

function Web3Login_generateRandomString($size_of_random_string = 8) {
	$char_list_str = array_merge(range('a', 'k'), range('m', 'z'), range('1', '9'), range('A', 'H'), range('J', 'N'), range('P', 'Z'));
 
	if ($size_of_random_string < 1) {
		return false;
	}
	if ($size_of_random_string === 1) {
		return $char_list_str[array_rand($char_list_str)];
	}
 
	$random_string = '';
	$i=0;
	while($i<$size_of_random_string){
		$random_string .= $char_list_str[array_rand($char_list_str)];
		$i++;
	}
	return $random_string;
}

function Web3Login_get_results_prepare($sql_exec,$arr){
	global $wpdb,$current_user;
	return $wpdb->get_results($wpdb->prepare($sql_exec,$arr),ARRAY_A);
}
function Web3Login_query_prepare($sql_exec,$arr,$tbl_id=0){
	global $wpdb,$current_user;
	$insert_id=0;
	$result=$wpdb->query($wpdb->prepare($sql_exec,$arr));
	$insert_id=$wpdb->insert_id;
	return $insert_id;
}	

function Web3Login_add_scripts() { 

	wp_enqueue_script( 'web3-1.7.0-web3.min.js', plugin_dir_url( __FILE__ ).'js/web3-1.7.0-web3.min.js', '', '20220302', true );
	wp_enqueue_script( 'web3modal-1.9.5-index.js', plugin_dir_url( __FILE__ ).'js/web3modal-1.9.5-index.js', '', '20220302', true );
	wp_enqueue_script( 'evm-chains-0.2.0-index.min.js', plugin_dir_url( __FILE__ ).'js/evm-chains-0.2.0-index.min.js', '', '20220302', true );
	wp_enqueue_script( 'web3-provider-1.7.1-index.min.js', plugin_dir_url( __FILE__ ).'js/web3-provider-1.7.1-index.min.js', '', '20220302', true );
	wp_enqueue_script( 'torus-embed-1.20.4-torus.umd.min.js', plugin_dir_url( __FILE__ ).'js/torus-embed-1.20.4-torus.umd.min.js', '', '20220302', true );
	wp_enqueue_script( 'main.js', plugin_dir_url( __FILE__ ).'js/main.js', '', '20220302', true );
}
add_action('login_enqueue_scripts', 'Web3Login_add_scripts');

function Web3Login_login_footer() {
	global $wp_version;
	$admin_url= admin_url( 'admin-ajax.php');
	$nonce=wp_create_nonce(Web3Login::CREDENTIAL_NAME);
	$web3Login_get_message_nonce=Web3Login_get_message($nonce);
	$html=<<<EOM
	<div id="connected" style="display: none">

	<button class="btn btn-primary" id="btn-disconnect">
	  Disconnect wallet
	</button>
	
	<hr>
	
	<div id="network">
	  <p>
		<strong>Connected blockchain:</strong> <span id="network-name"></span>
	  </p>
	
	  <p>
		<strong>Selected account:</strong> <span id="selected-account"></span>
	  </p>
	
	</div>
	
	<hr>
	
	<h3>All account balances</h3>
	
	<table class="table table-listing">
	  <thead>
		<th>Address</th>
		<th>balance</th>
	  </thead>
	
	  <tbody id="accounts">
	  </tbody>
	</table>
	
	<p>Please try to switch between different accounts in your wallet if your wallet supports this functonality.</p>
	
	</div>
	
		<!-- We use simple <template> templating for the example -->
		<div id="templates" style="display: none">
		  <template id="template-balance">
			<tr>
			  <th class="address"></th>
			  <td class="balance"></td>
			</tr>
		  </template>
		</div>
		<script>
			var ajaxurl = '$admin_url';
			function submitXMLHttpRequest(web3,address){
				web3.eth.personal.sign(web3.utils.utf8ToHex("$web3Login_get_message_nonce"),address,"$nonce").then(async (signature) => {
					const xhr = new XMLHttpRequest();
					xhr.open("POST", ajaxurl);
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					xhr.send(EncodeHTMLForm({
						action : 'Web3Login_sig_check'
						,address : address
						,signature : signature
						,_wpnonce : '$nonce'
					}));
					xhr.onreadystatechange = () =>{
						if (xhr.readyState == 4 && xhr.status == 200) {
							let obj = JSON.parse(xhr.responseText);
							if(typeof obj.url === 'string'){
								location.href=obj.url;
							}else if(typeof obj.err === 'string'){
								$('img.loading256').css({display:"none"});
								//$('a#a_send').css({display:"inline"});
								alert(obj.err);
							}else if(typeof obj.success === 'string'){
								$('img.loading256').css({display:"none"});
								//$('a#a_send').css({display:"inline"});
								//alert(obj.success);
							}

						}else if(xhr.status == 400){
							//console.log(xhr.responseText);
							let obj = JSON.parse(xhr.responseText);
							if(typeof obj.err === 'string'){
								alert(obj.err);
							}
						}else{
							alert('unknown error. please try again later.');
							console.log(xhr.responseText);
						}
					}
				});

			}
		</script>
		<a id="btn-disconnect" style='display:none;'></a>
		<style>
			a#btn-connect{
				line-height: 1em;
				display: inline-block;
				padding: 0.5em 0;
				margin: 1em 0 2em;
				height: 4.5em;
			}
		</style>
EOM;
		$ecrecover_cmd = get_option(Web3Login::PLUGIN_DB_PREFIX.'ecrecover_cmd');
		if(preg_match("/ecrecover/",$ecrecover_cmd)){
			$web3_signup_login=__("Web3 Sign-up/login","Web3Login");
			$plugin_dir_url=plugin_dir_url( __FILE__ );
			$msg_have_metamask=__("Have Metamask or any crypto wallets? If no, Select Torus to Web3 signup this site with your Google, Facebook, etc","Web3Login");
			$msg_how_to_signup=__("How to Sign-up this site?","Web3Login");
			$msg_just_click=__("Just click/tap above button, If you have Metamask or any web3 wallets, select yours. If no, select Torus to Sign-up this site with your Google, Facebook or other social accounts.","Web3Login");
			$html.=<<<EOM
				<script>
						let innerHTML="<p style='height:1em;clear:both;'>&nbsp;</p><p style='text-align:center;position:relative;'><a href='https://metamask.io/' target='_blank'><img src='{$plugin_dir_url}images/metamask-fox.svg' style='height:2em;' /></a>&nbsp;<a href='https://walletconnect.com/' target='_blank'><img src='{$plugin_dir_url}images/walletconnect-logo.png' style='height:1.5em;padding-bottom:0.25em;' /></a>&nbsp;<a href='https://toruswallet.io/' target='_blank'><img src='{$plugin_dir_url}images/torus-logo.png' style='height:1.5em;padding-bottom:0.25em;' /><a href='javascript:show_description();'><img src='{$plugin_dir_url}images/whatsthis.png' style='height: 1.2em; position: absolute; right: 0; top: 0.5em;' /></a></p><p id='p_web3_description' style='display:none;'>$msg_have_metamask</p><p><a class='button button-large' id='btn-connect' href='javascript:void(0);'>$web3_signup_login</a></p>";
						innerHTML="<p><a class='button button-large' id='btn-connect' href='javascript:void(0);'>";
						innerHTML+="<img src='{$plugin_dir_url}images/metamask-fox.svg' style='height:2em;' />";
						innerHTML+="<img src='{$plugin_dir_url}images/walletconnect-logo.png' style='height:1.5em;padding-bottom:0.25em;' />";
						innerHTML+="<img src='{$plugin_dir_url}images/torus-logo.png' style='height:1.5em;padding-bottom:0.25em;' />";
						innerHTML+="<br />$web3_signup_login</a></p>";
						innerHTML+="<p style='text-align:center;' id='how_to_signup'><a href='javascript:show_description();'>$msg_how_to_signup</a></p>";
						innerHTML+="<p id='p_web3_description' style='display:none;'>$msg_just_click</p>";
						innerHTML+="";
						document.getElementById("loginform").innerHTML=document.getElementById("loginform").innerHTML+innerHTML;
						function show_description(){
							if(document.getElementById("p_web3_description").style.display =='block'){
								document.getElementById("p_web3_description").style.display ='none';
							}else{
								document.getElementById("p_web3_description").style.display ='block';
							}
							
						}
				</script>

EOM;
		}
	return $html;
}
function Web3Login_login_head() {
	$html="";
	return $html;
}
function Web3Login_login_form() {
	$html=<<<EOM

<style>
#btn-connect{width:100%;text-align:center;}
</style>

EOM;
	return $html;
}

function Web3Login_get_message($nonce){
	return "Web3 login to https://{$_SERVER['SERVER_NAME']}, nonce:$nonce, ver=0.0.1";
}


add_action('init', 'Web3Login::init');

class Web3Login
{
	const VERSION           = '0.0.1';
	const PLUGIN_ID         = 'Web3Login';
	const CONFIG_MENU_SLUG  = self::PLUGIN_ID . '-config';
	const CREDENTIAL_ACTION = self::PLUGIN_ID . '-nonce-action';
	const CREDENTIAL_NAME   = self::PLUGIN_ID . '-nonce-key';
	const PLUGIN_DB_PREFIX  = self::PLUGIN_ID . '_';

	static function init()
	{
		return new self();
	}

	function __construct()
	{
		if (is_admin() && is_user_logged_in()) {
			load_plugin_textdomain( 'Web3Login', false, basename( dirname( __FILE__ ) ) . '/languages' );

			// メニュー追加
			add_action('admin_menu', [$this, 'set_plugin_menu']);
			//add_action('admin_menu', [$this, 'set_plugin_sub_menu']);

		}
	}

	function set_plugin_menu()
	{
		add_menu_page(
			self::PLUGIN_ID ." ". __("Settings"),           /* ページタイトル*/
			self::PLUGIN_ID ." ". __("Settings"),           /* メニュータイトル */
			'manage_options',         /* 権限 */
			self::PLUGIN_ID.'-settings',    /* ページを開いたときのURL */
			[$this, 'show_about_plugin'],       /* メニューに紐づく画面を描画するcallback関数 */
			'dashicons-format-gallery', /* アイコン see: https://developer.wordpress.org/resource/dashicons/#awards */
			99                          /* 表示位置のオフセット */
		);
	}
	function set_plugin_sub_menu() {

		add_submenu_page(
			self::PLUGIN_ID.'-settings',  /* 親メニュー */
			__("Settings"),
			__("Settings"),
			'manage_options',
			self::PLUGIN_ID.'-settings-config',
			[$this, 'show_config_form']);
	}

	function show_about_plugin() {
		global $current_user;
		
		?>
			<h1><?php echo esc_html(__("Settings of this plugin","Web3Login")); ?></h1>
		<?php
		
		$ecrecover_cmd = get_option(Web3Login::PLUGIN_DB_PREFIX.'ecrecover_cmd');
		if(preg_match("/ecrecover/",$ecrecover_cmd)){
			if (isset($_POST[self::CREDENTIAL_NAME]) && $_POST[self::CREDENTIAL_NAME]) {
				if (check_admin_referer(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME)) {

					$redirect_url=sanitize_url($_POST['redirect_url']);
					if ( preg_match( '/^http(s)?:\/\/[^\/\s]+(.*)$/', $redirect_url, $match ) ) {
						$redirect_url = $match[2];
					}
					if(preg_match('/^\/[\w\/:%#\$&\?\(\)~\.=\+\-]+/', $redirect_url, $arr00)){
						update_option(self::PLUGIN_DB_PREFIX . 'redirect_url', $redirect_url);
					}else{
						echo "failed. please specify correct URL.";
					}

			
				}
			}
	
			$redirect_url = get_option(Web3Login::PLUGIN_DB_PREFIX.'redirect_url');
			if(strlen($redirect_url)>0){
			}else{
				$redirect_url=get_edit_user_link($current_user->ID);
			}
			?>
				<form action="" method='post' id="my-submenu-form">
					<?php // ②：nonceの設定 ?>
					<?php wp_nonce_field(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME) ?>

					<p>
					<label for="redirect_url"><?php echo __("successfully login redirect URL","Web3Login"); ?> :</label>
					<input type="text" name="redirect_url" value="<?php echo  esc_url($redirect_url) ?>"/>
					</p>

					<p>&nbsp;</p>
					<p>
						Use shortcode? Add [web3login] to any pages/posts.
					</p>
					<p>&nbsp;</p>

					<p><input type='submit' value='<?php echo __("Save"); ?>' class='button button-primary button-large'></p>
				</form>

			<?php


		}else{
			$phpver=(float)phpversion();
			if((extension_loaded( 'gmp' ) || extension_loaded( 'bcmath' )) && $phpver>=7.1){
				update_option(Web3Login::PLUGIN_DB_PREFIX.'ecrecover_cmd',"elliptic-php/ecrecover.php");
				$redirect_url=get_edit_user_link($current_user->ID);
				if ( preg_match( '/^http(s)?:\/\/[^\/\s]+(.*)$/', $redirect_url, $match ) ) {
					$redirect_url = $match[2];
				}
				update_option(Web3Login::PLUGIN_DB_PREFIX.'redirect_url',$redirect_url);


				?>
					<script>
						location.reload();
					</script>
				<?php
			}else{
				?>
					<br /><br />
					
					<script>
						(function($) {
							$(function(){
								$('input#start_installation_npm').click(function(){
									$('img#install_msg_img').css({display:"block"});
									$('#start_installation_npm').css({display:"none"});
									$('pre#install_msg').text("Installing npm packages...\n");
									$.ajax({
										type: "POST",
										contentType: "application/x-www-form-urlencoded",
										url: '<?php echo admin_url( 'admin-ajax.php'); ?>',
										//which python{,3}
										data: "action=Web3Login_install_check&step=which_npm&_wpnonce=<?php echo wp_create_nonce(Web3Login::CREDENTIAL_NAME); ?>",
										success: function(data){
											let obj = JSON.parse(data);
											console.log(obj.success);
											$('pre#install_msg').text($('pre#install_msg').text()+obj.success);
											$('img#install_msg_img').css({display:"none"});
											if(typeof obj.reload === 'string'){
												location.reload();
											}else if(typeof obj.failed === 'string'){
												$('p.python3').css({display:"block"});
											}

										},
										error : function(a,b){
										}
									});

								});
								$('input#start_installation_python3').click(function(){
									$('img#install_msg_img').css({display:"block"});
									$('#start_installation_python3').css({display:"none"});
									$('pre#install_msg').text("Installing python3 packages...\n");
									$.ajax({
										type: "POST",
										contentType: "application/x-www-form-urlencoded",
										url: '<?php echo admin_url( 'admin-ajax.php'); ?>',
										//which python{,3}
										data: "action=Web3Login_install_check&step=which_python3&_wpnonce=<?php echo wp_create_nonce(Web3Login::CREDENTIAL_NAME); ?>",
										success: function(data){
											let obj = JSON.parse(data);
											console.log(obj);
											$('pre#install_msg').text($('pre#install_msg').text()+obj.success);
											$('img#install_msg_img').css({display:"none"});
											if(typeof obj.reload === 'string'){
												location.reload();
											}else if(typeof obj.failed === 'string'){
											}
										},
										error : function(a,b){
										}
									});

								});
							});
						})(jQuery);

					</script>
					<style>
						img#install_msg_img{display:none;height:2em;    filter: invert(0.75);}
					</style>
					<p class="install_msg"><pre id="install_msg"></pre><img id="install_msg_img" src='<?php echo plugin_dir_url( __FILE__ ); ?>images/loading256.gif' /></p>
					<?php
						$f_no_supprt=true;
						$cmd="node --version";
						$out=null; $ret=null;
						if(preg_match("/\d\.\d/",exec($cmd,$out,$ret))){
							$cmd="npm --version";
							$out=null; $ret=null;
							if(preg_match("/\d\.\d/",exec($cmd,$out,$ret))){
								$f_no_supprt=false;
								?>
									<style>
										p.python3{display:none;}
									</style>
									<p>This system supports nodejs:<br /><input type='button' id='start_installation_npm' value='<?php echo esc_html(__("Start Instration with nodejs","Web3Login")); ?>' class='button button-primary button-large'></p>
								<?php
							}
						}
						$cmd="which python{,3}";
						$out=null; $ret=null; $p_str="";
						if(exec($cmd,$out,$ret)){
							$str="";
							foreach($out as $python_path){
								if(preg_match("/\/python/",$python_path)){
									$cmd="$python_path --version";
									$out=null; $ret=null;
									if(preg_match("/ython 3\./",exec($cmd,$out,$ret))){
										$cmd="$python_path -m pip -V";
										$out=null; $ret=null;
										if(preg_match("/ython 3\./",exec($cmd,$out,$ret))){
											$f_no_supprt=false;
											$p_str="This system supports Python3";
										}
									}

								}
							}
						}
						if($f_no_supprt){
							?>
								<p><?php echo esc_html(__("Sorry, this version of this plugin needs [a] php7.1 higher & (bcmath or gmp) or [b] nodejs & npm or [c] python3 & pip3 on Unix system. Please ask your administrator to install php7.1 (+ bcmath/gmp), nodejs (+ npm) or python3 (+ pip3).","Web3Login")); ?></p>
							<?php
						}elseif($p_str!=""){
							?>
								<p class='python3'>This system supports Python3:<br /><input type='button' id='start_installation_python3' value='<?php echo esc_html(__("Start Instration with python3","Web3Login")); ?>' class='button button-primary button-large'></p></p>
							<?php

						}

			}

		}

	}

	/** 設定画面の表示 */
	function show_config_form() {
		// ① wp_optionsのデータをひっぱってくる
		$title = get_option(self::PLUGIN_DB_PREFIX . "title");
		?>
			<div class="wrap">
				<h1><?php echo __("Settings"); ?></h1>

				<form action="" method='post' id="my-submenu-form">
					<?php // ②：nonceの設定 ?>
					<?php wp_nonce_field(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME) ?>

					<p>
					<label for="title"><?php echo __("Title"); ?> :</label>
					<input type="text" name="title" value="<?php echo  $title ?>"/>
					</p>

					<p><input type='submit' value='<?php echo __("Save"); ?>' class='button button-primary button-large'></p>
				</form>
			</div>
		<?php
	}

	/** 設定画面の項目データベースに保存する */
}
?>