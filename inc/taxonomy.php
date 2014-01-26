<?php

class Flogger_Taxonomy {
	function __construct() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_filter( 'manage_edit-floggerexercise_columns', array( $this, 'manage_columns' ) );
		add_filter( 'manage_floggerexercise_custom_column', array( $this, 'add_column_content' ), 10, 3 );
		add_action( 'floggerexercise_add_form_fields', array( $this, 'add_units_field' ) );
		add_action( 'floggerexercise_edit_form_fields', array( $this, 'edit_units_field' ), 10, 2 );
		add_action( 'created_term', array( $this, 'save_term_data' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_term_data' ), 10, 3 );
	}
	
	static function get_term_meta( $term_id, $meta_key ) {
		return get_option( "floggerexercise_{$term_id}_{$meta_key}" );
	}

	static function set_term_meta( $term_id, $meta_key, $meta_value ) {
		update_option( "floggerexercise_{$term_id}_{$meta_key}", $meta_value );
	}

	function register_taxonomy() {
		$args = array(
			'public' => true,
			'label' => __( 'Exercises', 'flogger' )
		);
		register_taxonomy( 'floggerexercise', 'post', $args );
	}
	
	function manage_columns( $columns ){
		$columns['units'] = 'Units';
		unset( $columns['description'] );
		return $columns;
	}

	function add_column_content( $arg, $column_name, $term_id ){
		$content = self::get_term_meta( $term_id, $column_name );
		return $content;
	}
	
	function add_to_form( $field_value = '') {
?>
		<tr class="form-field">
			<th valign="top" scope="row">
				<label for="floggerunits"><?php echo esc_html__( 'Units', 'flogger' ); ?></label>
			</th>
			<td>
				<input type="text" name="floggerunits" value="<?php echo esc_attr( $field_value ); ?>"/>
				<p class="description"><?php echo esc_html__( 'Optional, e.g. steps, minutes', 'flogger' ); ?></p>
<?php
				wp_nonce_field( 'flogger_term', 'flogger_term_nonce' );
?>
			</td>
		</tr>
<?php 
	}
    
	function add_units_field( $taxonomy ) {
		$this->add_to_form();
	}
    
	function edit_units_field( $term, $taxonomy ) {
		$field_value = self::get_term_meta( $term->term_id, 'units' );
		$this->add_to_form( $field_value );
	}
    
	function save_term_data( $term_id, $term_taxonomy_id, $taxonomy ) {
		if ( ! isset( $_POST['flogger_term_nonce'] ) ) {
			return;
		}
		
		$nonce = $_POST['flogger_term_nonce'];
		
		if ( ! wp_verify_nonce( $nonce, 'flogger_term' ) ) {
			return;
		}

		if ( isset( $_POST['floggerunits'] ) ) {
			$units = sanitize_text_field( $_POST['floggerunits'] );
			self::set_term_meta( $term_id, 'units', $units );
		}
	}
}

$flogger_taxonomy = new Flogger_Taxonomy();