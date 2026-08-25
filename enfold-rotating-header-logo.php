<?php
/*
Plugin Name: Enfold Rotating Header Logo
Description: Randomly swaps the Enfold theme's header logo between a set of 2-5 images on every page load, entirely client-side so it works even with full-page caching enabled. No theme/child theme edits required.
Author: Lance Weaver
Version: 1.0
License: GPLv2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'RLE_OPTION_KEY', 'rle_logo_ids' );
define( 'RLE_MAX_LOGOS', 5 );
define( 'RLE_MIN_LOGOS', 2 );
define( 'RLE_VERSION', '1.0' );


// ── ADMIN SETTINGS PAGE ──────────────────────────────────────────────────────

add_action( 'admin_menu', 'rle_add_settings_page' );
function rle_add_settings_page() {
	add_options_page(
		'Random Logo',
		'Random Logo',
		'manage_options',
		'enfold-rotating-header-logo',
		'rle_render_settings_page'
	);
}

add_action( 'admin_init', 'rle_register_settings' );
function rle_register_settings() {
	register_setting( 'rle_settings_group', RLE_OPTION_KEY, array(
		'type'              => 'array',
		'sanitize_callback' => 'rle_sanitize_logo_ids',
		'default'           => array(),
	) );
}

function rle_sanitize_logo_ids( $input ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return get_option( RLE_OPTION_KEY, array() );
	}
	$ids = array();
	if ( is_array( $input ) ) {
		foreach ( $input as $value ) {
			$id = absint( $value );
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}
	}
	return array_slice( array_values( array_unique( $ids ) ), 0, RLE_MAX_LOGOS );
}

add_action( 'admin_enqueue_scripts', 'rle_admin_enqueue' );
function rle_admin_enqueue( $hook ) {
	if ( 'settings_page_enfold-rotating-header-logo' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'rle-admin',
		plugins_url( 'assets/admin.js', __FILE__ ),
		array( 'jquery' ),
		RLE_VERSION,
		true
	);
}

function rle_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to do this.' ) );
	}
	$logo_ids = get_option( RLE_OPTION_KEY, array() );
	?>
	<div class="wrap">
		<h1>Random Logo</h1>
		<p>Pick <?php echo esc_html( RLE_MIN_LOGOS ); ?>–<?php echo esc_html( RLE_MAX_LOGOS ); ?> images. On every page load, a visitor's browser will randomly pick one and swap it into the header logo spot (<code>.logo.avia-standard-logo img</code>). Works with page caching since the swap happens in the browser, not on the server.</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'rle_settings_group' ); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<?php for ( $i = 0; $i < RLE_MAX_LOGOS; $i++ ) :
						$id  = isset( $logo_ids[ $i ] ) ? (int) $logo_ids[ $i ] : 0;
						$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
						?>
						<tr>
							<th scope="row">Logo <?php echo esc_html( $i + 1 ); ?><?php echo ( $i < RLE_MIN_LOGOS ) ? ' (required)' : ' (optional)'; ?></th>
							<td>
								<div class="rle-logo-slot" data-index="<?php echo esc_attr( $i ); ?>">
									<img src="<?php echo esc_url( $src ); ?>" style="max-width:150px;max-height:80px;display:<?php echo $src ? 'block' : 'none'; ?>;margin-bottom:8px;" class="rle-preview" />
									<input type="hidden" name="<?php echo esc_attr( RLE_OPTION_KEY ); ?>[]" class="rle-id-field" value="<?php echo esc_attr( $id ); ?>" />
									<button type="button" class="button rle-select-btn">Select Image</button>
									<button type="button" class="button rle-remove-btn" style="<?php echo $src ? '' : 'display:none;'; ?>">Remove</button>
								</div>
							</td>
						</tr>
					<?php endfor; ?>
				</tbody>
			</table>

			<?php submit_button( 'Save Changes' ); ?>
		</form>
	</div>
	<?php
}


// ── FRONT-END: build the logo data + enqueue the swap script ────────────────

add_action( 'wp_enqueue_scripts', 'rle_enqueue_front_end' );
function rle_enqueue_front_end() {
	if ( is_admin() ) {
		return;
	}

	$logo_ids = get_option( RLE_OPTION_KEY, array() );
	$logo_ids = is_array( $logo_ids ) ? array_filter( array_map( 'absint', $logo_ids ) ) : array();

	if ( count( $logo_ids ) < RLE_MIN_LOGOS ) {
		return; // Not enough logos configured — leave the default theme logo alone.
	}

	$logos = array();
	foreach ( $logo_ids as $id ) {
		$src = wp_get_attachment_image_url( $id, 'full' );
		if ( ! $src ) {
			continue; // Attachment was deleted since it was selected.
		}
		$meta   = wp_get_attachment_metadata( $id );
		$width  = isset( $meta['width'] ) ? (int) $meta['width'] : '';
		$height = isset( $meta['height'] ) ? (int) $meta['height'] : '';
		$srcset = wp_get_attachment_image_srcset( $id, 'full' );
		$sizes  = $width ? '(max-width: ' . $width . 'px) 100vw, ' . $width . 'px' : '';
		$alt    = get_post_meta( $id, '_wp_attachment_image_alt', true );

		$logos[] = array(
			'src'    => $src,
			'srcset' => $srcset ? $srcset : '',
			'sizes'  => $sizes,
			'width'  => $width,
			'height' => $height,
			'alt'    => $alt,
		);
	}

	if ( count( $logos ) < RLE_MIN_LOGOS ) {
		return;
	}

	wp_register_script(
		'rle-front',
		plugins_url( 'assets/random-logo.js', __FILE__ ),
		array(),
		RLE_VERSION,
		true
	);
	wp_localize_script( 'rle-front', 'rleLogos', $logos );
	wp_enqueue_script( 'rle-front' );
}
