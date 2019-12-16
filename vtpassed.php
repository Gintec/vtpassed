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

add_action('wp_head','head_code');

function head_code()
{
$output = '<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">';
$output .= '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>';    
$output .= '<script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>'; 
$output .= '<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>';
$output .='<script src="https://js.paystack.co/v1/inline.js"></script>';

echo $output;

}

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
$amount = $_POST['amount'];
$recepient = $_POST['recepient'];
$serviceID = $_POST['serviceID'];
?>

<h3>Enter Payment (Card) Information</h3>
	<hr>
	<p>
		<?php echo strtoupper($_POST['serviceID']); ?> Recharge of <?php echo $_POST['amount']; ?> Naira Airtime to : <?php echo $_POST['recepient']; ?>
	</p>
    <div id="paystackEmbedContainer"></div>
	<div id="topup"></div>
</div>

    <script>
            PaystackPop.setup({
            key: 'pk_test_9013537b2fb7899691364c7e25e68256303d3a97',
            email: 'info@igreenmall.com',
            amount: <?php echo $_POST['amount']; ?>00,
            container: 'paystackEmbedContainer',
            callback: function(response){
				if(response.status=="success"){
					
					$("#topup").html('<h4 style="color: green;">Your payment was successful please click Continue to complete your transaction.</h4><form action="" method="post"><input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>"><input type="hidden" name="recepient" value="<?php echo $recepient; ?>"><input type="hidden" name="amount" value="<?php echo $amount; ?>"><input type="submit" name="Topup2" value="Continue" class="btn btn-success"></form>');
				}    
                },
            });  
    </script>
	<input action="action" onclick="window.history.go(-1); return false;" type="submit" value="Go Back" />



<?php }else
	if(isset($_POST['Topup2'])){
    $username = "info@igreenmall.com"; //email address(sandbox@vtpass.com)
    $password = "Pureweb2$"; //password (sandbox)
	$host = "https://vtpass.com/api/pay";
    //$host = 'http://sandbox.vtpass.com/api/payflexi';

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
$vdata = curl_exec($curl);
curl_close($curl);   
$res = json_decode($vdata , true); 
// var_dump($res);
// Insert to Database

	if($res['code']=="000"){
		$amount = $_POST['amount'];
		$dated = date("Y-m-d H:m:s");
		$status = $res['response_description'];
		$product = $res['content']['transactions']['product_name'];
		$particulars = $res['content']['transactions']['transactionId']." - ".$res['requestId'];
	  $wpdb->query("INSERT INTO $table_name(name,ttype,user_email,tid,amount,dated,status) VALUES('$name','$product','$user_email','$particulars','$amount','$dated','$status')");
	}else{
		$wpdb->query("INSERT INTO $table_name(name,ttype,user_email,tid,amount,dated,status) VALUES('$name','Airtime','$user_email',0,'-',NOW(),'$status')");
	}
  if($status=="TRANSACTION SUCCESSFUL"){
      $astatus = "success";
      $tstatus = "successful (".$res['response_description'].")";
  }else{
    $astatus = "danger";
    $tstatus = "NOT successful (".$res['response_description'].")";
  }
  ?>

  <div class="alert alert-<?php echo $astatus; ?>" role="alert">
        <h3><?php echo $status; ?></h3><hr>
        <p>Your airtime recharge was <?php echo $tstatus; ?></p>
  </div>
<?php }else{ ?>
	<h2>Recharge Phone Airtime</h2>

    <hr>
    <form action="" method="post">
		
        <input type="hidden" name="Topup" value="Topup">
        <div class="row">
           
            <div class="col-md-6 form-group">
                <label for="amount">Enter Amount</label>
                <input id="amount" class="form-control" type="number" name="amount" placeholder="Enter Amount" value="0" required>
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
                <button type="submit" class="btn btn-primary"  onclick="payWithPaystack()">Go</button>                
            </div>
        </div>
		
    </form>
	<?php }

?>
<?php } );

add_shortcode('tv_subscription', function(){
    global $wpdb; 
    $table_name = $wpdb->prefix . 'vtus';
    if(isset($_POST['Subscribe'])){ 
    $amount = $_POST['amount'];
    $recepient = $_POST['recepient'];
    $serviceID = $_POST['serviceID'];
    $variation_code = $_POST['variation_code'];
    $billersCode = $_POST['billersCode'];
    ?>
    
    <h3>Enter Payment (Card) Information</h3>
        <hr>
        <p>
            <?php echo strtoupper($_POST['serviceID']); ?> Subscription of <?php echo $_POST['amount']; ?> Naira  to : <?php echo $_POST['billersCode']; ?>
        </p>
        <div id="paystackEmbedContainer"></div>
        <div id="topup"></div>
    </div>
    
        <script>
                PaystackPop.setup({
                key: 'pk_test_9013537b2fb7899691364c7e25e68256303d3a97',
                email: 'info@igreenmall.com',
                amount: <?php echo $_POST['amount']; ?>00,
                container: 'paystackEmbedContainer',
                callback: function(response){
                    if(response.status=="success"){
                        
                        $("#topup").html('<h4 style="color: green;">Your payment was successful please click Continue to complete your transaction.</h4><form action="" method="post"><input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>"><input type="hidden" name="recepient" value="<?php echo $recepient; ?>"><input type="hidden" name="amount" value="<?php echo $amount; ?>"><input type="hidden" name="billersCode" value="<?php echo $billersCode; ?>"><input type="hidden" name="variation_code" value="<?php echo $variation_code; ?>"><input type="submit" name="Subscribe2" value="Continue" class="btn btn-success"></form>');
                    }    
                    },
                });  
        </script>
        <input action="action" onclick="window.history.go(-1); return false;" type="submit" value="Go Back" />
    
    
    
    <?php }else
        if(isset($_POST['Subscribe2'])){
        $username = "info@igreenmall.com"; //email address(sandbox@vtpass.com)
        $password = "Pureweb2$"; //password (sandbox)
        $host = "https://vtpass.com/api/service-variations";
       
        //$host = 'http://sandbox.vtpass.com/api/payflexi';
    
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
        'billersCode'=> $_POST['billersCode'], // e.g smartcardNumber, meterNumber,
      	'variation_code'=> $_POST['variation_code'], // e.g dstv1, dstv2,prepaid,(optional for somes services)
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
    $vdata = curl_exec($curl);
    curl_close($curl);   
    $res = json_decode($vdata , true); 
    // var_dump($res);
    // Insert to Database
    
        if($res['code']=="000"){
            $amount = $_POST['amount'];
            $dated = date("Y-m-d H:m:s");
            $status = $res['response_description'];
            $product = $res['content']['transactions']['type'];
            $particulars = $res['content']['transactions']['transactionId']." - ".$res['requestId'];
          $wpdb->query("INSERT INTO $table_name(name,ttype,user_email,tid,amount,dated,status) VALUES('$name','$product','$user_email','$particulars','$amount','$dated','$status')");
        }else{
            $wpdb->query("INSERT INTO $table_name(name,ttype,user_email,tid,amount,dated,status) VALUES('$name','TV Subscription','$user_email',0,'-',NOW(),'$status')");
        }
      if($status=="TRANSACTION SUCCESSFUL"){
          $astatus = "success";
          $tstatus = "successful (".$res['response_description'].")";
      }else{
        $astatus = "danger";
        $tstatus = "NOT successful (".$res['response_description'].")";
      }
      ?>
    
      <div class="alert alert-<?php echo $astatus; ?>" role="alert">
            <h3><?php echo $status; ?></h3><hr>
            <p>Your TV Subscription was <?php echo $tstatus; ?></p>
      </div>
    <?php }else{ ?>
        <h2>Pay TV Subscription</h2>
    
        <hr>
        <form action="" method="post">
            
            <input type="hidden" name="Topup" value="Topup">
            <div class="row">
               
                <div class="col-md-6 form-group">
                    <label for="amount">Enter Amount</label>
                    <input id="amount" class="form-control" type="number" name="amount" placeholder="Enter Amount" value="0" required>
                </div>
    
                <div class="col-md-6 form-group">
                    <label for="operator">Select TV Network</label>
                    
                    <select name="serviceID" id="operator" class="form-control" required="required">
                        <option value="dstv">DSTV Subscription</option>
                        <option value="gotv" selected>GOTV Payment</option>
                        <option value="startimes">Startimes Subscription</option>                        
                    </select>
                    
                </div>
                
            </div>

            <div class="row">
               
                <div class="col-md-6 form-group">
                    <label for="billersCode">Enter SmartCard number</label>
                    <input id="billersCode" class="form-control" type="number" name="billersCode" placeholder="Enter SmartCard number" value="0" required>
                </div>
    
                <div class="col-md-6 form-group">
                    <label for="operator">Select Bouquet</label>
                    <?php
                
                $startimes = wp_remote_retrieve_body( wp_remote_get( 'https://vtpass.com/api/service-variations?serviceID=startimes' ) );

						
					?>
					
					<select name="variation_code">
                    <option value="Select Bouquet" selected>Select Bouquet</option>
                    <optgroup label="Startimes Bouquets">
                        <?php
                           
                            foreach($startimes['content']['variations'] as $vars){
                                echo "<option value=".$vars->code.">".$vars->code."</option>";
                            }

                        ?>
                    </optgroup>
                    </select>
                             
                </div>
                
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="phone">Enter Phone Number</label>
                    <input id="phone" class="form-control" type="number" name="recepient" placeholder="Enter Phone Number" required>
                </div>
                <div class="col-md-6 form-group">                
                    <button type="submit" class="btn btn-primary"  onclick="payWithPaystack()">Go</button>                
                </div>
            </div>
            
        </form>
        <?php }
    
    ?>
    <?php } );
    
