<?php 
add_action( 'admin_init', array( $this, 'setup_section' ) );
?>

<form method="POST">
<?php wp_nonce_field( 'awesome_update', 'awesome_form' ); ?>
<table class="form-table">