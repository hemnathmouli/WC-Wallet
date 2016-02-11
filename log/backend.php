<?php
if(!ABSPATH){
	exit;
}
?>
<div class = "wrap">
<h1>Wallet / Credits Logs</h1>
<table class = "wp-list-table widefat fixed striped posts">
	<thead>
		<tr>
			<th scope = "col">Transaction ID</th>
			<th scope = "col">User</th>
			<th scope = "col">Order Number</th>
			<th scope = "col">Type</th>
			<th scope = "col">Credits</th>
			<th scope = "col">Date</th>
		</tr>
	</thead>
	<?php foreach( wc_w_get_log() as $log ){
	$user_info = get_userdata( $log["uid"] );
    ?>
	<tr>
		<td><?php echo $log["ID"]; ?></td>
		<td><a href = '<?php echo home_url()."/wp-admin/user-edit.php?user_id=".$log["uid"]?>' title = "View Profile"><?php echo $user_info->user_login; ?></a></td>
		<td><a href = '<?php echo home_url()."/wp-admin/post.php?post=".$log["oid"]."&action=edit"; ?>' title = "View Order"><?php echo "#".$log["oid"]." - View Order"; ?></a></td>
		<td><?php wc_w_get_type( $log["wcw_type"] ); ?></td>
		<td><?php echo wc_price( $log["amount"] );  ?></td>
		<td><?php echo $log["date"]?></td>
	<tr>
	<?php }?>
	<tfoot>
		<tr>
			<th scope = "col">Transaction ID</th>
			<th scope = "col">User</th>
			<th scope = "col">Order Number</th>
			<th scope = "col">Type</th>
			<th scope = "col">Credits</th>
			<th scope = "col">Date</th>
		</tr>
	</tfoot>
</table>
</div>
