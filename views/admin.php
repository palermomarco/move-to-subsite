<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e( 'Move to Subsite', 'mw-move-to-subsite' ) ?></h2>
	<form class="under" style="margin-top:21px" method="post">
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Choose Category: ', 'mw-move-to-subsite' ) ?></th>
				<td><?php wp_dropdown_categories( 'hide_empty=0' ) ?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Move Page &amp; Children', 'mw-move-to-subsite' ) ?></th>
				<td>
					<?php wp_dropdown_pages( 'show_option_none=' . __( 'Choose Page (Optional)', 'mw-move-to-subsite' ) ) ?>
					<p class="description"><?php _e('If chosen, all children of this page will be moved to the new site and will become top-level pages.') ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Move to existing blog') ?></th>
				<td>
					<?php MW_Move_Base::blogs_dropdown(); ?>
				</td>
			</tr>
			<tr class="maybe-hide">
				<th scope="row"><?php _e( 'Name of New Blog: ', 'mw-move-to-subsite' ) ?></th>
				<td><input type="text" name="blog_name" /></td>
			</tr>
			<tr class="maybe-hide">
				<th scope="row"><?php _e( 'Path for blog: ', 'mw-move-to-subsite' ) ?></th>
				<td>
					<input type="text" name="blog_path" />
					<p class="description"><?php 
					if ( is_subdomain_install() ) :
						_e( 'This will be <code>http://yourdomain.com/PATH</code>.', 'mw-move-to-subsite' );
					else :
						_e( 'This will be <code>http://PATH.yourdomain.com</code>.', 'mw-move-to-subsite' );
					endif; ?></p>
				</td>
			</tr>
			<tr class="maybe-hide">
				<th scope="row"><?php _e( 'Admin for new blog' ) ?></th>
				<td>
					<?php wp_dropdown_users( array(
						'show_option_none' => __( 'Choose user (optional)', 'mw-move-to-subsite' ),
						'show_user' => 'user_login',
						'name' => 'user_id'
					) ); ?>
					<p class="description"><?php _e( 'Optional. If no user is chosen, the currently logged-in user (you) will be the admin of the new blog.', 'mw-move-to-subsite' ); ?></p>
				</td>
			</tr>
		</table>
		<?php 
		MW_Move_Base::nonce_field();
		submit_button( __( 'Move to Subsite!', 'mw-move-to-subsite' ) ) ?>
	</form>
</div>
<script>
	jQuery(document).ready(function($){
		$("select[name='existing_blog']").change(function(){
			var toHide = $(".maybe-hide")
			if ( $(this).val() > 0 ) {
				toHide.hide()
			}
			else {
				toHide.show()
			}
		}).change()
	})
</script>