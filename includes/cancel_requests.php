<div class = "wrap">
<?php 
	if( !is_cancel_request_enabled() ){
		wcw_plugin_success_msg('To have this feature, please enable cancel requests in settings under WC Wallet menu',"error");
	}
?>
<h1>Cancel Requests</h1>
<?php 
if( is_cancel_request_enabled() ){
?>
HI
<?php 
}
?>
</div>