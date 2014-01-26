<?php

class Flogger_Post {
	function __construct() {
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post_data' ) );
	}

	function filter_the_content( $content ) {
		global $post;
		$terms = wp_get_post_terms( $post->ID, 'floggerexercise' );
		$first_term = true;
		
		foreach ( (array) $terms as $term ) {
			$field_name = '_flogger_post_meta_' . $term->term_id;
			$field_value = get_post_meta( $post->ID, $field_name, true );
			$field_units = Flogger_Taxonomy::get_term_meta( $term->term_id, 'units' );
			if ( $first_term && ! empty ( $content ) ) {
				$content .= "<hr/>";
			}
			if ( $first_term ) {
				$content .= "<p>" . esc_html__( 'Exercise on this day', 'flogger' ) . ": ";
			} else {
				$content .= " &middot; ";
			}
			$content .= $term->name;
			if ( ! empty( $field_value ) ) {
				$content .= ": " . $field_value;
				if ( ! empty( $field_units ) ) {
					$content .= " " . $field_units;
				}
			}
			$first_term = false;
		}
		
		if ( ! $first_term ) {
			$content .= "</p>";
		}
		
		return $content;
	}
	
	function add_meta_box() {
		add_meta_box( 'floggerpostmetabox', __( 'Fitness Log', 'flogger' ),
			array( $this, 'emit_meta_box' ), 'post' );
	}
	
	function emit_meta_box( $post ) {
		wp_nonce_field( 'flogger_post_meta', 'flogger_post_meta_nonce' );
		
		$has_terms = false;
		$args = array();
		$terms = wp_get_post_terms( $post->ID, 'floggerexercise', $args );
		
		$has_terms = ( ! empty( $terms ) );
		
		if ( $has_terms ) {
			echo "<table><tbody>";
		
			foreach ( (array) $terms as $term ) {
				$field_name = '_flogger_post_meta_' . $term->term_id;
				$field_label = $term->name;
				$field_value = get_post_meta( $post->ID, $field_name, true );
?>
				<tr class="form-field">
					<th valign="top" scope="row">
						<label for="<?php echo esc_attr( $field_name ); ?>"><?php echo esc_html( $field_label ); ?></label>
					</th>
					<td>
						<input type="text" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>"/> 
						<?php echo esc_html( Flogger_Taxonomy::get_term_meta( $term->term_id, 'units' ) ) ?>
					</td>
				</tr>
<?php
			}
		
			echo "</tbody></table>";
		} else {
			echo "<p>";
			echo esc_html__( 'No exercises have been tagged for this post.  Add an exercise using the Exercises meta box.', 'flogger' );
			echo "</p>";
		}
	}
	
	function save_post_data( $post_id ) {
		if ( ! isset( $_POST['flogger_post_meta_nonce'] ) ) {
			return $post_id;
		}
		
		$nonce = $_POST['flogger_post_meta_nonce'];
		
		if ( ! wp_verify_nonce( $nonce, 'flogger_post_meta' ) ) {
			return $post_id;
		}
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
		
		$args = array();
		$terms = wp_get_post_terms( $post_id, 'floggerexercise', $args );
		foreach ( (array) $terms as $term ) {
			$field_name = '_flogger_post_meta_' . $term->term_id;
			$field_value = sanitize_text_field( $_POST[$field_name] );
			if ( ! empty( $field_value ) ) {
				update_post_meta( $post_id, $field_name, $field_value );
			} else {
				delete_post_meta( $post_id, $field_name );
			}
		}
		
		return $post_id;
	}
}

$flogger_post = new Flogger_Post();
