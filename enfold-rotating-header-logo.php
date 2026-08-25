<?php
/*
Plugin Name: Enfold Rotating Header Logo
Description: Randomly swaps the Enfold theme's header logo between a set of 2-5 images on every page load, entirely client-side so it works even with full-page caching enabled. No theme/child theme edits required.
Author: Lance Weaver
Version: 1.1
License: GPLv2
GitHub Plugin URI: lance-weaver/Enfold-rotating-header-logo
GitHub Branch: master
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'RLE_OPTION_KEY', 'rle_logo_ids' );
define( 'RLE_ERROR_OPTION_KEY', 'rle_last_error' );
define( 'RLE_MAX_LOGOS', 5 );
define( 'RLE_MIN_LOGOS', 2 );
define( 'RLE_VERSION', '1.1' );
define( 'RLE_SETTINGS_SLUG', 'enfold-rotating-header-logo' );
define( 'RLE_GITHUB_REPO_URL', 'https://github.com/lance-weaver/Enfold-rotating-header-logo' );


// ── PLUGIN LIST "SETTINGS" LINK ──────────────────────────────────────────────

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'rle_plugin_action_links' );
function rle_plugin_action_links( $links ) {
	$settings_url  = admin_url( 'options-general.php?page=' . RLE_SETTINGS_SLUG );
	$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}


// ── ADMIN SETTINGS PAGE ──────────────────────────────────────────────────────

add_action( 'admin_menu', 'rle_add_settings_page' );
function rle_add_settings_page() {
	add_options_page(
		'Rotating Logo',
		'Rotating Logo',
		'manage_options',
		RLE_SETTINGS_SLUG,
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
	if ( 'settings_page_' . RLE_SETTINGS_SLUG !== $hook ) {
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

// Dismiss-error action (nonce-protected, capability-checked).
add_action( 'admin_post_rle_dismiss_error', 'rle_handle_dismiss_error' );
function rle_handle_dismiss_error() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to do this.' ) );
	}
	check_admin_referer( 'rle_dismiss_error' );
	delete_option( RLE_ERROR_OPTION_KEY );
	wp_safe_redirect( admin_url( 'options-general.php?page=' . RLE_SETTINGS_SLUG ) );
	exit;
}

function rle_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to do this.' ) );
	}
	$logo_ids   = get_option( RLE_OPTION_KEY, array() );
	$logo_count = is_array( $logo_ids ) ? count( array_filter( $logo_ids ) ) : 0;
	$last_error = get_option( RLE_ERROR_OPTION_KEY, false );
	?>
	<div class="wrap">
		<h1>Rotating Logo</h1>

		<div class="notice notice-info" style="padding:12px 16px;">
			<p>
				<strong>Enfold Rotating Header Logo</strong> v<?php echo esc_html( RLE_VERSION ); ?> —
				created by <strong>Lance Weaver</strong>. Source code, install/update instructions, and
				issue tracking live on GitHub:
				<a href="<?php echo esc_url( RLE_GITHUB_REPO_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( RLE_GITHUB_REPO_URL ); ?></a>
			</p>
			<p>
				<strong>To update this plugin:</strong> push your changes to the GitHub repo above, bump the
				<code>Version</code> number in the plugin header, and publish a matching GitHub Release/tag
				(e.g. <code>v1.2</code>). If the <a href="https://github.com/afragen/git-updater" target="_blank" rel="noopener noreferrer">Git Updater</a>
				plugin is installed, WordPress's normal "update available" notice on the Plugins page will pick
				it up automatically. Otherwise, just download the latest code as a ZIP from the repo page and
				re-upload it via <em>Plugins → Add New → Upload Plugin</em> — WordPress will offer to
				"Replace current with uploaded" and your settings (stored in the database, not the plugin files)
				are preserved either way.
			</p>
		</div>

		<?php if ( $last_error ) : ?>
			<div class="notice notice-error" style="padding:12px 16px;">
				<p>
					<strong>⚠ Error:</strong> <?php echo esc_html( $last_error['message'] ); ?>
					<?php if ( ! empty( $last_error['time'] ) ) : ?>
						<em>(<?php echo esc_html( $last_error['time'] ); ?>)</em>
					<?php endif; ?>
				</p>
				<p>
					The site is safely showing Enfold's normal configured logo while this error is present.
					You should fix this in the GitHub repo and re-upload the updated plugin.
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'rle_dismiss_error' ); ?>
					<input type="hidden" name="action" value="rle_dismiss_error" />
					<?php submit_button( 'Dismiss', 'secondary', 'submit', false ); ?>
				</form>
			</div>
		<?php endif; ?>

		<p>
			<?php if ( $logo_count >= RLE_MIN_LOGOS ) : ?>
				<strong>Status:</strong> Active — <?php echo esc_html( $logo_count ); ?> logo(s) configured, rotating on every page load.
			<?php else : ?>
				<strong>Status:</strong> Inactive — fewer than <?php echo esc_html( RLE_MIN_LOGOS ); ?> logos are selected below, so the site is showing
				Enfold's normal logo (whatever is configured in Enfold's own Theme Options) as a fallback. Select at least
				<?php echo esc_html( RLE_MIN_LOGOS ); ?> images to activate rotation.
			<?php endif; ?>
		</p>

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

	// Fail-safe: any unexpected error here must never break the front end.
	// On failure we log it for the settings page and simply don't enqueue anything,
	// which leaves Enfold's own configured logo displayed untouched.
	try {
		$logo_ids = get_option( RLE_OPTION_KEY, array() );
		$logo_ids = is_array( $logo_ids ) ? array_filter( array_map( 'absint', $logo_ids ) ) : array();

		if ( count( $logo_ids ) < RLE_MIN_LOGOS ) {
			return; // Not enough logos configured — fall back to Enfold's own configured logo.
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
			return; // Fewer valid attachments than required — fall back to Enfold's own logo.
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
	} catch ( \Throwable $e ) {
		update_option( RLE_ERROR_OPTION_KEY, array(
			'message' => $e->getMessage(),
			'time'    => current_time( 'mysql' ),
		) );
		return; // Fall back to Enfold's own configured logo.
	}
}
