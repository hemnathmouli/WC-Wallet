<?php
if(!ABSPATH){
	exit;
}

if( isset( $_POST["submit"] ) ){
	if( wcw_update_form( $_POST ) ){
		wcw_plugin_success_msg( "Settings Saved." );
	}	
}

$wcw_payment_method 		= explode( ",",get_option('wcw_payment_method') );
$wcw_apply_tax_yes 			= get_option('wcw_apply_tax') == 1 				? 'checked' : '';
$wcw_apply_tax_no 			= get_option('wcw_apply_tax') == 0 				? 'checked' : '';
$wcw_notify_admin 			= get_option('wcw_notify_admin') == 1 			? 'checked' : '';
$wcw_remining_credits 		= get_option('wcw_remining_credits') == 1 		? 'checked' : '';
$wcw_restrict_max 			= get_option('wcw_restrict_max');
$wcw_cancel_req 			= get_option('wcw_cancel_req') == 1 			? 'checked' : '';
$wcw_automatic_cancel_req 	= get_option('wcw_automatic_cancel_req') == 1 	? 'checked' : '';
$wcw_notify_on_cancel_req 	= get_option('wcw_notify_on_cancel_req') == 1 	? 'checked' : '';

?>
<style>
.checkboxes{
	width: 100%;
	float: left;
	margin-top: 10px;
}
</style>
<div class = "wrap">
<h1>WC Wallet Settings</h1>
<form method="post">
	<h3 class="title">Gendral Settings</h3>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="wcw_payment_method">Credits Applicable for</label></th>
			<td>
				<?php 
					$array = new WC_Payment_Gateways();
					$methods = $array->get_available_payment_gateways();
					if( count($methods) != 0 ){
						foreach( $methods as $key => $method ){
				?>
						<label class = "checkboxes" for="dashboard_right_now-hide_<?php echo $key;?>">
							<input class="hide-postbox-tog" name="wcw_payment_method[]" type="checkbox" <?php if( in_array($key, $wcw_payment_method) ){ echo "checked"; } ?> id="dashboard_right_now-hide_<?php echo $key;?>" value="<?php echo $key; ?>">
							<?php echo $method->title; ?>						
						</label>
				<?php 
						}
					}else{
						echo "No Payment Methods found.!";
					}
				?>
				<p class="description" id="tagline-description">When order is cancelled, if the above checked method are there, the cancel request can be processed.</p>
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				<label for="wcw_apply_tax">Apply Credits For Tax?</label>
			</th>
			<td>
				<input type = "radio" id = "wcw_apply_tax" name = "wcw_apply_tax" value = "1" <?php echo $wcw_apply_tax_yes; ?>> Yes 
				<input type = "radio" id = "wcw_apply_tax" name = "wcw_apply_tax" value = "0" <?php echo $wcw_apply_tax_no; ?>> No
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				<label for="wcw_restrict_max">Restrict Maximum credits</label>
			</th>
			<td>
				<input name="wcw_restrict_max" type="number" id="wcw_restrict_max" value = "<?php echo $wcw_restrict_max; ?>" class="regular-text">
				<p class="description" id="tagline-description">This can restrict maximum credits usage for a person. Leave blank for no restrict.</p>
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				<label for="wcw_notify_admin">Notify Admin</label>
			</th>
			<td>
				<input type = "checkbox" id = "wcw_notify_admin" name = "wcw_notify_admin" value = "1" <?php echo $wcw_notify_admin; ?>> Yes 
				<p class="description" id="tagline-description">Notify admin if any changes happen in user wallet.</p>
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				<label for="wcw_remining_credits">Show Credits Remining In Cart</label>
			</th>
			<td>
				<input type = "checkbox" id = "wcw_remining_credits" name = "wcw_remining_credits" <?php echo $wcw_remining_credits; ?> value = "1"> Yes 
				<p class="description" id="tagline-description">Hide or Show the remaining credits available for a user in the cart page.</p>
			</td>
		</tr>
		
	</table>
	
	<h3 class="title">Send Cancel Request Option</h3>
	<table class="form-table">		
		<tr>
			<th scope="row">
				<label for="wcw_cancel_req">Enable "Send Cancel Request"</label>
			</th>
			<td>
				<input type = "checkbox" id = "wcw_cancel_req" name = "wcw_cancel_req" <?php echo $wcw_cancel_req; ?> value = "1"> Yes 
				<p class="description" id="tagline-description">Enabling this will add a button called "Send Cancel Request" in my-account page.</p>
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				<label for="wcw_automatic_cancel_req">Automatically Cancel Order On "Send Cancel Request"</label>
			</th>
			<td>
				<input type = "checkbox" id = "wcw_automatic_cancel_req" name = wcw_automatic_cancel_req <?php echo $wcw_automatic_cancel_req; ?> value = "1"> Yes 
				<p class="description" id="tagline-description">If this option is not enabled, you need to customly click refund under Cancel Request Page.</p>
			</td>
		</tr>
		
		<tr>
			<th scope="row">
				<label for="wcw_notify_on_cancel_req">Send Email If New Cancel Request Received</label>
			</th>
			<td>
				<input type = "checkbox" id = "wcw_notify_on_cancel_req" name = "wcw_notify_on_cancel_req" <?php echo $wcw_notify_on_cancel_req; ?> value = "1"> Yes 
				<p class="description" id="tagline-description">Send e-mail to admin if an cancel request is received.</p>
			</td>
		</tr>
	</table>
	
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
	</p>
</form>
</div>