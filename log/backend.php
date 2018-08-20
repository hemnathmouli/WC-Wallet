<?php
if (! ABSPATH) {
	exit ();
}
?>

<div class="wrap">
	<h1>Wallet / Credits Logs</h1>
	<style>
th:first-child input[type="checkbox"] {
	margin-left: 0px;
}

.delete-all-logs {
	margin-bottom: 20px !important;
	position: relative;
	top: 1px;
}
</style>

	<script>
$ = jQuery;
$(document).ready(function(){
	$(document).on("click", ".check-all-logs", function() {
		if ( $(".check-all-logs").attr("checked") == "checked" ) {
			$(".check-all-logs").attr("checked", "checked");
			$(".each-all-logs").each (function(){
				$(this).attr("checked", "checked");
			});
		} else {
			$(".check-all-logs").removeAttr("checked");
			$(".each-all-logs").each (function(){
				$(this).removeAttr("checked");
			});
		}

		if ( $(".each-all-logs:checked").length == 0 ) {
			$(".delete-all-logs").attr("disabled", "");
		} else {
			$(".delete-all-logs").removeAttr("disabled");
		}
	});

	$(document).on("change", ".each-all-logs", function(){
		if ( $(".each-all-logs").length == $(".each-all-logs:checked").length ) {
			$(".check-all-logs").attr("checked", "checked");
		} else {
			$(".check-all-logs").removeAttr("checked");
		}

		if ( $(".each-all-logs:checked").length == 0 ) {
			$(".delete-all-logs").attr("disabled", "");
		} else {
			$(".delete-all-logs").removeAttr("disabled");
		}
	});	

	$(document).on("click", ".delete-all-logs:not([disabled])", function() {
		if ( confirm('Are you sure do you want to delete selected items? This cannot be reverted.') ) {
			var ids = new Array();
			$(".each-all-logs:checked").each(function(){
				ids.push($(this).val());
			});
			var params = {
				    type: 'POST',
				    url: "<?php echo admin_url('admin-ajax.php'); ?>",         
				    data:  {
				       	"action" 	  : 'delete_credit_logs',
				       	"wcw_ids"	  : ids
					},
				    dataType: 'json',
				    timeout: 30000,
				    beforeSend : function(){  
				    	$(".wcw-deleting").html("&nbsp;&nbsp;&nbsp;Deleting..");
				    },
				    success: function( res ) {
					    if ( res.status == true ) {
				    		$(".wcw-deleting").html("&nbsp;&nbsp;&nbsp;Successfully deleted selected items");
				    		window.location = "";		
					    }    	
				    },
				    error : function(){
				    	
				    }    		    
			    };
				$.ajax( params ); 
		} else {
		    // Do nothing!
		}
	});
});
</script>

	<form method="get">
		<div class="alignleft actions">
			<select name="ID" id="filter-by-date">
				<option value="0">All Users</option>
			<?php
			$array = array ();
			foreach ( wc_w_get_log () as $log ) {
				if (! in_array ( $log ["uid"], $array )) {
					$array [] = $log ["uid"];
					$user_info = get_userdata ( $log ["uid"] );
					?><option value="<?php echo $user_info->ID; ?>"
					<?php if( isset( $_GET['filter_action'] ) && $_GET['ID'] == $user_info->ID ){ echo "selected = 'selected'";  }?>><?php echo $user_info->user_login; ?></option><?php
				}
				?>
			
			<?php } ?>
		</select> <select name="filter_type" class="dropdown_product_cat">
				<option value="2">Type</option>
				<option class="level-0" value="0"
					<?php if( isset( $_GET['filter_action'] ) && $_GET['filter_type'] == 0 ){ echo "selected = 'selected'";  }?>>Wallet
					to Credits</option>
				<option class="level-0" value="1"
					<?php if( isset( $_GET['filter_action'] ) && $_GET['filter_type'] == 1 ){ echo "selected = 'selected'";  }?>>Credits
					to Wallet</option>
			</select> <input type="submit" name="filter_action"	id="post-query-submit" class="button" value="Filter"> 
			<a href="javascript:void(0);" title="Delete Selected" disabled	class="button button-primary delete-all-logs"><?php _e( 'Delete Selected', WC_WALLET_TEXT  ); ?></a>
			<span class = "wcw-deleting"></span>
		</div>
		<input type="hidden" name="page" value="wallet" />
	</form>
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th><input type="checkbox" class="check-all-logs"></th>
				<th scope="col"><?php _e("Transaction ID", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("User", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("Order Number", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("Type", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("Credits", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("Date", WC_WALLET_TEXT ); ?></th>
			</tr>
		</thead>
	<?php
	if (isset ( $_GET ['filter_action'] ) && $_GET ['filter_action'] == "Filter") {
		foreach ( wc_w_get_log () as $log ) {
			$filter_id = $_GET ['ID'];
			$filter_type = $_GET ['filter_type'];
			if (($filter_id == 0 || $filter_id == $log ['uid']) && ($filter_type == 2 || $filter_type == $log ["wcw_type"])) {
				$user_info = get_userdata ( $log ["uid"] );
				?>
			<tr>
			<td><input type="checkbox" class='each-all-logs' value="<?php echo $log["ID"]; ?>"></td>
			<td><?php echo $log["ID"]; ?></td>
			<td><a	href='<?php echo home_url()."/wp-admin/user-edit.php?user_id=".$log["uid"]?>' title="View Profile"><?php echo $user_info->user_login; ?></a></td>
			<td>
				<?php if ( $log["oid"] ): ?>
					<a	href='<?php echo home_url()."/wp-admin/post.php?post=".$log["oid"]."&action=edit"; ?>'	title="View Order"><?php echo "#".$log["oid"]." - View Order"; ?></a>
				<?php endif; ?>
			</td>
			<td><?php wc_w_get_type( $log["wcw_type"] ); ?></td>
			<td><?php echo wc_price( $log["amount"] );  ?></td>
			<td><?php echo $log["date"]; ?></td>
		
		
		<tr>
			<?php
			
}
		}
	} else {
		foreach ( wc_w_get_log () as $log ) {
			$user_info = get_userdata ( $log ["uid"] );
			?>
	
		
		
		<tr>
			<td><input type="checkbox" class='each-all-logs' value="<?php echo $log["ID"]; ?>"></td>
			<td><?php echo $log["ID"]; ?></td>
			<td><a	href='<?php echo home_url()."/wp-admin/user-edit.php?user_id=".$log["uid"]?>'title="View Profile"><?php echo $user_info->user_login; ?></a></td>
			<td>
				<?php if ( $log["oid"] ): ?>
					<a	href='<?php echo home_url()."/wp-admin/post.php?post=".$log["oid"]."&action=edit"; ?>' title="View Order"><?php echo "#".$log["oid"]." - View Order"; ?></a>
				<?php endif; ?>
			</td>
			<td><?php wc_w_get_type( $log["wcw_type"] ); ?></td>
			<td><?php echo wc_price( $log["amount"] );  ?></td>
			<td><?php echo $log["date"]; ?></td>
		
		
		<tr>
	<?php
		
}
	}
	?>
	
		
		
		<tfoot>
			<tr>
				<th><input type="checkbox" class="check-all-logs"></th>
				<th scope="col"><?php _e("Transaction ID", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("User", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("Order Number", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("Type", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("Credits", WC_WALLET_TEXT ); ?></th>
				<th scope="col"><?php _e("Date", WC_WALLET_TEXT ); ?></th>
			</tr>
		</tfoot>
	</table>
</div>
