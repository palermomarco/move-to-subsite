<style type="text/css">
div.album {
    float:left;
    width:200px;
    height:150px;
    margin-right:15px;
}
div.album td {
    font-size:0.9em;
}
div.album-hidden img {
    opacity:0.5;
}
.form-table {
	max-width: 850px;
	float: left;
	clear: none;
	margin: 0 40px 20px 0;
}
.form-table th h3 {
	margin: 0;
}
.wps3-author {
	width: 250px;
	float: left;
	padding: 20px;
	border: 1px solid #ccc;
	overflow: hidden;
	margin: 0 0 40px 0;
}
.wps3-author img {
	float: left;
	margin-right: 20px;
	border-radius: 32px;
}
.wps3-author .desc {
	float: left;
}
.wps3-author h3 {
	font-size: 12px;
	margin: 0;
}
.wps3-author h2 {
	font-size: 18px;
	margin: 0;
	padding: 0;
}
.wps3-author h2 a {
	color: #464646;
	text-decoration: none;
}
.wps3-author h2 a:hover {
	color: #000;
}
.wps3-author p {
	margin: 0;
}
.wps3-author .github {
	padding-top: 5px;
}
</style>


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
        
        <div class="wps3-author">
            <img src="http://www.gravatar.com/avatar/a2611b745e80bbe85ae0cacb8b621e64?s=128&amp;d" width="64" height="64" />
            <div class="desc">
                <h3>Maintained by</h3>
                <h2>Marco Palermo</h2>
                <p>
                    <a href="http://profiles.wordpress.org/palermomarco">Profile</a>
                    &nbsp;&nbsp;
                    <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=9EV95JP2E5WXA">Donate</a>
                </p>
                <p class="github">
                    <a href="https://github.com/palermomarco/move-to-subsite/">Contribute on GitHub</a>
                </p>
            </div>
        </div>
        
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