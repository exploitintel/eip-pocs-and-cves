<div class="main-wrapper">
	<h2>PVS MANAGEMENT</h2>	
	<?php	
	$results = pvs_fetch_all_posts_voted_for();
	$total_page_links = $results[ 0 ];
	$voted_posts = $results[ 1 ];
	if ( is_array( $voted_posts ) && empty( $voted_posts ) ){		
	?>
	<table class="wp-list-table widefat" cellspacing="0">
		<tr>
			<th>None Of Your Posts Has Been Voted Yet</th>
		</tr>
	</table>
	<?php
	}else{
		$nonce = wp_create_nonce( 'reset_multiple_post_vote_counter' );
		$post_url = admin_url( 'admin.php?action=reset_multiple_post_vote_counter_081613&_nonce=' . $nonce );
	?>
	<form name="reset_vote_counter_form" id="reset_vote_counter_form" action="<?php echo $post_url; ?>" method="post">
		<table class="wp-list-table widefat" cellspacing="0">
			<thead>
				<tr>
					<th align="center" valign="top" style="text-align:center;">
						<input type="checkbox" name="all_reset_selector" id="all_reset_selector"/>
					</th>
					<th align="center" valign="top" style="text-align:center;">Post Name</th>
					<th align="center" valign="top" style="text-align:center;">Likes</th>
					<th align="center" valign="top" style="text-align:center;">Dislikes</th>
					<th align="center" valign="top" style="text-align:center;">Operations</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td align="center" valign="top">
						<select name="group_op_selector" id="group_op_selector">
							<option value="0" selected="selected">-Select-</option>
							<option value="1">Reset</option>
						</select>
					</td>
					<td colspan="4" align="center" valign="top" style="padding:5px;">
					<?php						
					if ( $total_page_links ){				
					?>					
						<table class="wp-list-table widefat fixed posts" style="width:50%;margin:10px;">
							<tr align="center">
							<?php pvs_display_page_links( $total_page_links ); ?>						
							</tr>
						</table>				
					<?php				
					}				
					?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			foreach( $voted_posts as $voted_post ){
			?>
				<tr id="row_<?php echo $voted_post[ 'post_id' ]; ?>">
					<th align="center" valign="top" style="text-align:center;">
						<input type="checkbox" name="reset_selector[]" class="reset_selector"/>
						<input type="hidden" name="post_id[]" value="<?php echo $voted_post[ 'post_id' ]; ?>"/>
					</th>
					<td align="center" valign="top" style="text-align:center;"><?php echo pvs_get_post_name( $voted_post[ 'post_id' ] ); ?></td>
					<td align="center" valign="top" style="text-align:center;"><?php echo $voted_post[ 'upvote_count' ]; ?></td>
					<td align="center" valign="top" style="text-align:center;"><?php echo $voted_post[ 'downvote_count' ]; ?></td>
					<td align="center" valign="top" style="text-align:center;"><a href="javascript:void(0);" class="button button-primary button-large reset_button">Reset Counter</a></td>
				</tr>
			<?php
			}
			?>
			</tbody>
		</table>
	</form>
	<?php
	}
	?>	
</div>