<?php
if(!ABSPATH){
	exit;
}
?>

<div class = "wrap">
<h1>Wallet / Credits Logs</h1>

<form method  = "get">
	<div class="alignleft actions">
		<label for="filter-by-date" class="screen-reader-text">Filter by date</label>
		<select name="ID" id="filter-by-date">
			<option value="0">All Users</option>
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
		<select name="filter_type" class="dropdown_product_cat">
			<option value="2">Type</option>
			<option class="level-0" value="0" <?php if( isset( $_GET['filter_action'] ) && $_GET['filter_type'] == 0 ){ echo "selected = 'selected'";  }?>>Wallet to Credits</option>
			<option class="level-0" value="1" <?php if( isset( $_GET['filter_action'] ) && $_GET['filter_type'] == 1 ){ echo "selected = 'selected'";  }?>>Credits to Wallet</option>
		</select>
		<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">
	</div>
	<input type = "hidden" name ="page" value = "wallet"/>
</form>
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
	<?php 
	if( isset( $_GET['filter_action'] ) && $_GET['filter_action'] == "Filter" ){
		foreach( wc_w_get_log() as $log ){
			$filter_id 		= $_GET['ID'];
			$filter_type	= $_GET['filter_type'];	
			if( ( $filter_id == 0 || $filter_id == $log['uid']) && ( $filter_type == 2 || $filter_type == $log["wcw_type"]  ) ){
			$user_info = get_userdata( $log["uid"] );
			?>
			<tr>
				<td><?php echo $log["ID"]; ?></td>
				<td><a href = '<?php echo home_url()."/wp-admin/user-edit.php?user_id=".$log["uid"]?>' title = "View Profile"><?php echo $user_info->user_login; ?></a></td>
				<td><a href = '<?php echo home_url()."/wp-admin/post.php?post=".$log["oid"]."&action=edit"; ?>' title = "View Order"><?php echo "#".$log["oid"]." - View Order"; ?></a></td>
				<td><?php wc_w_get_type( $log["wcw_type"] ); ?></td>
				<td><?php echo wc_price( $log["amount"] );  ?></td>
				<td><?php echo $log["date"]; ?></td>
			<tr>
			<?php }
		}
	}else{
	foreach( wc_w_get_log() as $log ){
	$user_info = get_userdata( $log["uid"] );
    ?>
	<tr>
		<td><?php echo $log["ID"]; ?></td>
		<td><a href = '<?php echo home_url()."/wp-admin/user-edit.php?user_id=".$log["uid"]?>' title = "View Profile"><?php echo $user_info->user_login; ?></a></td>
		<td><a href = '<?php echo home_url()."/wp-admin/post.php?post=".$log["oid"]."&action=edit"; ?>' title = "View Order"><?php echo "#".$log["oid"]." - View Order"; ?></a></td>
		<td><?php wc_w_get_type( $log["wcw_type"] ); ?></td>
		<td><?php echo wc_price( $log["amount"] );  ?></td>
		<td><?php echo $log["date"]; ?></td>
	<tr>
	<?php }
	}?>
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
