<div class = "wrap">
<?php 
	if( !is_cancel_request_enabled() ){
		wcw_plugin_success_msg(__( 'To have this feature, please enable cancel requests in settings under WC Wallet menu', WC_WALLET_TEXT ),"error");
	}
?>

<h1>Cancel Requests</h1>
<?php 
if( is_cancel_request_enabled() ){
?>

<form method  = "get">
	<div class="alignleft actions">
		<label for="filter-by-date" class="screen-reader-text"><?php _e( 'Filter by date', WC_WALLET_TEXT ); ?></label>
		<select name="ID" id="filter-by-date">
			<option value="0"><?php _e( 'All Users', WC_WALLET_TEXT ); ?></option>
			<?php 
			$array = array();
			foreach( wc_w_get_log() as $log ){ 
			if( !in_array( $log["uid"], $array ) ){
				$array[] = $log["uid"]; 
				$user_info = get_userdata( $log["uid"] ); 
				?><option value="<?php echo $user_info->ID; ?>" <?php if( isset( $_GET['filter_action'] ) && $_GET['ID'] == $user_info->ID ){ echo "selected = 'selected'";  }?>><?php echo $user_info->user_login; ?></option><?php
			}
			?>
			
			<?php } ?>
		</select> 
		<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">
	</div>
	<input type = "hidden" name ="page" value = "wc-wallet-cancel-requests"/>
</form>
<table class = "wp-list-table widefat fixed striped posts">
	<thead>
		<tr>
			<th scope = "col"><?php _e( 'Transaction ID', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'User', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'Order Number', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'Amount', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'Date', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'Refund', WC_WALLET_TEXT ); ?></th>
		</tr>
	</thead>
	<?php 
	if( wc_w_get_cancel_requests() != 0 ){
		if( isset( $_GET['filter_action'] ) && $_GET['filter_action'] == "Filter" ){
			foreach( wc_w_get_cancel_requests() as $log ){
			$user_info = get_userdata( $log["uid"] );
			$filter_id 		= $_GET['ID'];
			if( $filter_id == 0 || $filter_id == $log['uid'] ){
		    ?>
			<tr>
				<td><?php echo $log["ID"]; ?></td>
				<td><a href = '<?php echo home_url()."/wp-admin/user-edit.php?user_id=".$log["uid"]?>' title = "View Profile"><?php echo $user_info->user_login; ?></a></td>
				<td><a href = '<?php echo home_url()."/wp-admin/post.php?post=".$log["oid"]."&action=edit"; ?>' title = "View Order"><?php echo "#".$log["oid"]." - View Order"; ?></a></td>
				<td><?php echo wc_price( $log["amount"] );  ?></td>
				<td><?php echo $log["date"]?></td>
				<td class = "wp-core-ui">
				<?php 
					$refund = $log['refund'] == 0 ? '<a href = "'.admin_url('admin-ajax.php?action=wcw_refund_order&order_id=' . $log["oid"] .'&pid=' . $log["ID"] ).'" id="post-query-submit" class="button">Refund as Credits</a>' : '<a href = "javascript:void(0);" id="post-query-submit" style="background: rgba(120, 119, 119, 0.21);" class="button">Already Redunded</a>'; 
					echo $refund;
				?>
				</td>
			<tr>
			<?php 
			}
			}
		}else{
			foreach( wc_w_get_cancel_requests() as $log ){
				$user_info = get_userdata( $log["uid"] );
				?>
				<tr>
					<td><?php echo $log["ID"]; ?></td>
					<td><a href = '<?php echo home_url()."/wp-admin/user-edit.php?user_id=".$log["uid"]?>' title = "View Profile"><?php echo $user_info->user_login; ?></a></td>
					<td><a href = '<?php echo home_url()."/wp-admin/post.php?post=".$log["oid"]."&action=edit"; ?>' title = "View Order"><?php echo "#".$log["oid"]." - View Order"; ?></a></td>
					<td><?php echo wc_price( $log["amount"] );  ?></td>
					<td><?php echo $log["date"]?></td>
					<td class = "wp-core-ui">
					<?php 
						$refund = $log['refund'] == 0 ? '<a href = "'.admin_url('admin-ajax.php?action=wcw_refund_order&order_id=' . $log["oid"] .'&pid=' . $log["ID"] ).'" id="post-query-submit" class="button">Refund as Credits</a>' : '<a href = "javascript:void(0);" id="post-query-submit" style="background: rgba(120, 119, 119, 0.21);" class="button">Already Redunded</a>'; 
						echo $refund;
					?>
					</td>
				<tr>
				<?php 
				}
		}
	}
	?>
	<tfoot>
		<tr>
			<th scope = "col"><?php _e( 'Transaction ID', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'User', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'Order Number', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'Amount', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'Date', WC_WALLET_TEXT ); ?></th>
			<th scope = "col"><?php _e( 'Refund', WC_WALLET_TEXT ); ?></th>
		</tr>
	</tfoot>
</table>
<?php 
}
?>
</div>