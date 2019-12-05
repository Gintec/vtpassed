<?php
/*
Plugin Name:  VTPASS ADMIN
Plugin URI: https://www.gintec.com.ng
Description: A simple plugin for carrying out virtual topup and payment services.
Version: 1.0.0
Author: Tony Nwokoma
Author URI: https://www.tonynwokoma.net/
License: GPL2
*/
register_activation_hook( __FILE__, 'vtpassedOperationsTable');
function vtpassedOperationsTable() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'vtus';
    $sql = "CREATE TABLE `$table_name` (
    `tsn` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(220) DEFAULT NULL,
    `ttype` varchar(220) DEFAULT NULL,
    `user_email` varchar(220) DEFAULT NULL,
    `tid` varchar(220) DEFAULT NULL,
    `amount` varchar(220) DEFAULT NULL,
    `dated` varchar(220) DEFAULT NULL,
    `status` varchar(220) DEFAULT NULL,
    PRIMARY KEY(tsn)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
    ";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function my_scripts() {
    wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
    wp_enqueue_script( 'boot1','https://code.jquery.com/jquery-3.3.1.slim.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot2','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot3','https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ),'',true );
}
add_action( 'admin_enqueue_scripts', 'my_scripts' );

add_action('admin_menu', 'addAdminPageContent');
function addAdminPageContent() {
  add_menu_page('VPASSED', 'VT PASSED Manager', 'manage_options' ,__FILE__, 'vtpassedAdminPage', 'dashicons-wordpress');
}
function vtpassedAdminPage() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vtus';
  ?>
  <div class="wrap">
    <?php
    if(isset($_GET['deltrans'])){
        $tsn = $_GET['deltrans'];
        $wpdb->query("DELETE FROM $table_name WHERE tsn='$tsn'");
        ?>
        <div class="alert alert-success" role="alert">
        
        <p>The Record has been deleted successfully!</p>
        </div>
    <?php }
    ?>
    <h2>VIEW TRANSACTIONS</h2>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Transaction Particulars</th>
          <th>Status</th>
          <th>Type</th>
          <th>Amount</th>
          <th>Date</th>
		<th>Delete</th>
        </tr>
      </thead>
      <tbody>
    <?php
           

            $result = $wpdb->get_results("SELECT * FROM $table_name ORDER BY dated DESC");
            
            foreach ($result as $print) {?>
                <tr>
                    <td><?php echo $print->name; ?></td>
                    <td><?php echo $print->user_email; ?></td>
                    <td><?php echo $print->tid; ?></td>
                    <td><?php echo $print->status; ?></td>
                    <td><?php echo $print->ttype; ?></td>
                    <td><?php echo $print->amount; ?></td>
					<td><?php echo $print->dated; ?></td>
					<td><a href="<?php echo esc_url( add_query_arg( 'deltrans', $print->tsn ) ); ?>">Delete</a></td>
                </tr>
            <?php }

      ?>
      </tbody>  
    </table>
    <br>
    
  </div>
  <?php
}

add_shortcode('topup_vtu', function(){
global $wpdb; 
$table_name = $wpdb->prefix . 'vtus';
if(isset($_POST['Topup'])){
    $username = "gintecservices@gmail.com"; //email address(sandbox@vtpass.com)
    $password = "8610prayer"; //password (sandbox)
    $host = 'http://sandbox.vtpass.com/api/payflexi';

if ( is_user_logged_in() ) {

    global $current_user;
    get_currentuserinfo();
    
        /* echo 'Username: ' . $current_user->user_login . "
    ";
        echo 'User email: ' . $current_user->user_email . "
    ";
        echo 'User first name: ' . $current_user->user_firstname . "
    ";
        echo 'User last name: ' . $current_user->user_lastname . "
    ";
        echo 'User display name: ' . $current_user->display_name . "
    ";
        echo 'User ID: ' . $current_user->ID . "        ";
    */
    
    $vtuser = $current_user->user_nicename."-";
    $name = $vtuser = $current_user->user_nicename;
    $user_email = $current_user->user_email;
}else{
    $vtuser = "Guest-";
    $name = "Guest";
    $user_email = "Guest";
}

$data = array(
    'serviceID'=> $_POST['serviceID'], //integer e.g mtn,airtel
    'amount' =>  $_POST['amount'], // integer
    'phone' => $_POST['recepient'], //integer
    'request_id' => strtoupper($vuser).substr(md5(uniqid(mt_rand(), true).microtime(true)),0, 8)
);
$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_URL => $host,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_USERPWD => $username.":" .$password,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
));
echo $vdata = curl_exec($curl);
curl_close($curl);   
$res = json_decode($vdata , true); 

// Insert to Database

	if($res[0]->code=="000"){
		$amount = $res[0]->content[0]->amount;
		$dated = $res[0]->content[0]->created_date[0]->date;
		$status = $res[0]->content[0]->response_description;
	  $wpdb->query("INSERT INTO $table_name(name,ttype,user_email,tid,amount,dated,status) VALUES('$name','Airtime','$user_email',0,'$amount','$dated','$status')");
	}else{
		$wpdb->query("INSERT INTO $table_name(name,ttype,user_email,tid,amount,dated,status) VALUES('$name','Airtime','$user_email',0,'-',NOW(),'Error')");
	}
  if($status=="TRANSACTION SUCCESSFUL"){
      $astatus = "success";
      $tstatus = "successful";
  }else{
    $astatus = "danger";
    $tstatus = "NOT successful";
  }
  ?>

  <div class="alert alert-<?php echo $astatus; ?>" role="alert">
        <h3><?php echo $status; ?></h3><hr>
        <p>Your airtime recharge was <?php echo $tstatus; ?></p>
  </div>
<?php }

?>
    <h2>Recharge Phone Airtime</h2>
    <hr>
    <form action="" method="post">
        <input type="hidden" name="Topup" value="Topup">
        <div class="row">
           
            <div class="col-md-6 form-group">
                <label for="amount">Enter Amount</label>
                <input id="amount" class="form-control" type="number" name="Enter Amount" required>
            </div>

            <div class="col-md-6 form-group">
                <label for="operator">Select Operator</label>
                
                <select name="serviceID" id="operator" class="form-control" required="required">
                    <option value="airtel">Airtel Airtime VTU</option>
                    <option value="mtn" selected>MTN Airtime VTU</option>
                    <option value="9mobile">9Mobile Airtime VTU</option>
                    <option value="glo">Glo Mobile VTU</option>
                    <option value="smile">Smile Network Payment</option>
                </select>
                
            </div>
            
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="phone">Enter Phone Number</label>
                <input id="phone" class="form-control" type="number" name="recepient" placeholder="Enter Phone Number" required>
            </div>
            <div class="col-md-6 form-group">                
                <button type="submit" class="btn btn-primary">Go</button>                
            </div>
        </div>

        
    </form>
<?php } );


add_shortcode('tv_vtu', function(){
global $wpdb; 
$table_name = $wpdb->prefix . 'vtus';
if(isset($_POST['tvsub'])){

    $serviceid =  $_POST['serviceID'];
    $billerscode =  $_POST['billersCode'];
    $variation_code =  $_POST['variation_code'];
    $username = "gintecservices@gmail.com"; //email address(sandbox@vtpass.com)
    $password = "8610prayer"; //password (sandbox)
    $host = 'http://sandbox.vtpass.com/api/payfix';

if ( is_user_logged_in() ) {

    global $current_user;
    get_currentuserinfo();
    
        /* echo 'Username: ' . $current_user->user_login . "
    ";
        echo 'User email: ' . $current_user->user_email . "
    ";
        echo 'User first name: ' . $current_user->user_firstname . "
    ";
        echo 'User last name: ' . $current_user->user_lastname . "
    ";
        echo 'User display name: ' . $current_user->display_name . "
    ";
        echo 'User ID: ' . $current_user->ID . "        ";
    */
    
    $vtuser = $current_user->user_nicename."-";
    $name = $vtuser = $current_user->user_nicename;
    $user_email = $current_user->user_email;
}else{
    $vtuser = "Guest-";
    $name = "Guest";
    $user_email = "Guest";
}

$data = array(
    'serviceID'=> $_POST['serviceID'], //integer e.g gotv,dstv,eko-electric,abuja-electric
    'billersCode'=> $_POST['billersCode'], // e.g smartcardNumber, meterNumber,
    'variation_code'=> $_POST['variation_code'], // e.g dstv1, dstv2,prepaid,(optional for somes services)
    'amount' =>  $_POST['amount'], // integer (optional for somes services)
    'phone' => $_POST['recepient'], //integer
    'request_id' => strtoupper($billerscode).substr(md5(uniqid(mt_rand(), true).microtime(true)),0, 8)
);

$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_URL => $host,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_USERPWD => $username.":" .$password,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data,
));
echo $vdata = curl_exec($curl);
curl_close($curl);   
$res = json_decode($vdata , true); 

// Insert to Database

	if($res[0]->code=="000"){
		$amount = $res[0]->content[0]->amount;
		$dated = $res[0]->content[0]->created_date[0]->date;
		$status = $res[0]->content[0]->response_description;
	  $wpdb->query("INSERT INTO $table_name(name,ttype,user_email,tid,amount,dated,status) VALUES('$name','$serviceid.$variation_code','$user_email',0,'$amount','$dated','$status')");
	}else{
		$wpdb->query("INSERT INTO $table_name(name,ttype,user_email,tid,amount,dated,status) VALUES('$name','$serviceid.$variation_code','$user_email',0,'-',NOW(),'Error')");
	}
  if($status=="TRANSACTION SUCCESSFUL"){
      $astatus = "success";
      $tstatus = "successful";
  }else{
    $astatus = "danger";
    $tstatus = "NOT successful";
  }
  ?>

  <div class="alert alert-<?php echo $astatus; ?>" role="alert">
        <h3><?php echo $status; ?></h3><hr>
        <p>Your airtime recharge was <?php echo $tstatus; ?></p>
  </div>
<?php }

?>
    <h2>Pay TV Subscription</h2>
    <hr>
    <form action="" method="post">
        <input type="hidden" name="tvsub" value="tvsub">
        <div class="row">
           
            <div class="col-md-6 form-group">
                <label for="amount">Enter Amount</label>
                <input id="amount" class="form-control" type="number" name="Enter Amount" required>
            </div>

            <div class="col-md-6 form-group">
                <label for="operator">Select Provider</label>
                
                <select name="serviceID" id="operator" class="form-control" required="required">
                    <option value="dstv">GOTV Payment</option>
                    <option value="gotv" selected>DSTV Subscription</option>
                    <option value="startimes">Startimes Subscription</option>
                    
                </select>
                
            </div>
            
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="billerscode">Enter Smartcard Number</label>
                <input id="billerscode" class="form-control" type="number" name="billerscode" placeholder="Enter Phone Number" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="phone">Select Category</label>
                <select name="variation_code" id="variation_code">
                    <option value="dstv1">DSTV 1</option>
                    <option value="dstv2">DSTV 2</option>
                    <option value="gotv-lite">GOTV Lite</option>
                    <option value="gotv-value">GOTV Value</option>
                    <option value="nova">Startimes Nova</option>
                    <option value="basic">Startimes Basic</option>
                </select>
            </div>
            
        </div>

        <div class="row">
        <div class="col-md-6 form-group">                
                <button type="submit" class="btn btn-primary">Go</button>                
            </div>
        </div>

        
    </form>
<?php } );
