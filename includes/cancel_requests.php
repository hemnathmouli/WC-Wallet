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
<table class = "wp-list-table widefat fixed striped posts">
	<thead>
		<tr>
			<th scope = "col">Transaction ID</th>
			<th scope = "col">User</th>
			<th scope = "col">Order Number</th>
			<th scope = "col">Amount</th>
			<th scope = "col">Date</th>
			<th scope = "col">Refund</th>
		</tr>
	</thead>
	<?php 
	if( wc_w_get_cancel_requests() != 0 ){
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
	?>
	<tfoot>
		<tr>
			<th scope = "col">Transaction ID</th>
			<th scope = "col">User</th>
			<th scope = "col">Order Number</th>
			<th scope = "col">Amount</th>
			<th scope = "col">Date</th>
			<th scope = "col">Refund</th>
		</tr>
	</tfoot>
</table>
<?php 
}
?>
</div>