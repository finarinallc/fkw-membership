<div class="wrap fkwmembership-form-fields">
	<h1><?php echo FKWMEMBERSHIP_NAME; ?></h1>
	<?php settings_errors( $this->fkwmembership->plugin_namespace . '-messages' ); ?>
	<form action="options.php" method="post">
		 <?php
		 settings_fields( $this->settings_page_id );
		 do_settings_sections( $this->settings_page_id );
		 submit_button( __( 'Save Settings', $this->fkwmembership->plugin_namespace ) );
		 ?>
	</form>
</div>
