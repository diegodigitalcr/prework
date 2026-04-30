<?php
/**
 * Plugin Name: WhatsApp Link Generator
 * Plugin URI:  https://github.com/diegodigitalcr/prework
 * Description: Genera un enlace de WhatsApp con mensaje personalizado. Insértalo en cualquier página o entrada con el shortcode [whatsapp_link_generator].
 * Version:     1.0.0
 * Author:      Diego Digital
 * License:     GPL-2.0-or-later
 * Text Domain: whatsapp-link-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WLG_VERSION', '1.0.0' );
define( 'WLG_DIR', plugin_dir_path( __FILE__ ) );
define( 'WLG_URL', plugin_dir_url( __FILE__ ) );

class WhatsApp_Link_Generator {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_shortcode( 'whatsapp_link_generator', array( $this, 'render_shortcode' ) );
	}

	public function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'whatsapp_link_generator' ) ) {
			wp_enqueue_style(
				'wlg-style',
				WLG_URL . 'assets/style.css',
				array(),
				WLG_VERSION
			);
			wp_enqueue_script(
				'wlg-script',
				WLG_URL . 'assets/script.js',
				array(),
				WLG_VERSION,
				true
			);
		}
	}

	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'       => 'Generador de Enlace de WhatsApp con Mensaje Personalizado',
				'button_text' => 'Generar enlace',
				'placeholder_phone'   => 'Ej. +1 743 2331 2342',
				'placeholder_message' => 'Quiero más información',
			),
			$atts,
			'whatsapp_link_generator'
		);

		ob_start();
		?>
		<div class="wlg-wrap">

			<h2 class="wlg-title"><?php echo esc_html( $atts['title'] ); ?></h2>

			<p class="wlg-notice">
				<strong>📌 IMPORTANTE:</strong> Debes ingresar el código del país seguido de tu número de teléfono.
			</p>

			<div class="wlg-inner">

				<!-- Form side -->
				<div class="wlg-form-side">
					<div class="wlg-brand">
						<svg class="wlg-wa-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48">
							<path fill="#25D366" d="M24 4C12.955 4 4 12.955 4 24c0 3.576.948 6.93 2.607 9.823L4 44l10.406-2.573A19.88 19.88 0 0 0 24 44c11.045 0 20-8.955 20-20S35.045 4 24 4z"/>
							<path fill="#fff" d="M35.176 31.68c-.477 1.348-2.37 2.467-3.873 2.792-.516.11-1.19.198-3.456-.742-2.9-1.2-5.357-3.273-7.3-5.845-.884-1.178-1.482-2.52-1.482-3.985 0-1.334.51-2.527 1.35-3.414.384-.413.83-.635 1.258-.635h.885c.38 0 .714.228.857.578l1.24 3.034c.143.35.05.756-.234 1.01l-.664.592c-.313.28-.35.746-.085 1.066 1.06 1.294 2.3 2.338 3.68 3.045.366.19.823.123 1.108-.163l.68-.684c.27-.27.673-.36 1.032-.227l2.912 1.09c.38.142.627.507.627.91v.578z"/>
						</svg>
						<span class="wlg-brand-name">WhatsApp</span>
					</div>

					<div class="wlg-field">
						<label for="wlg-phone">Tu número de teléfono</label>
						<input
							type="tel"
							id="wlg-phone"
							class="wlg-input"
							placeholder="<?php echo esc_attr( $atts['placeholder_phone'] ); ?>"
							autocomplete="off"
						/>
						<p class="wlg-field-hint">
							No olvides agregar el código del país al número de teléfono.
							<a href="https://www.countrycode.org/" target="_blank" rel="noopener noreferrer">Más detalles</a>
						</p>
					</div>

					<div class="wlg-field">
						<label for="wlg-message">Texto del mensaje</label>
						<input
							type="text"
							id="wlg-message"
							class="wlg-input"
							placeholder="<?php echo esc_attr( $atts['placeholder_message'] ); ?>"
						/>
					</div>

					<button id="wlg-btn" class="wlg-button" type="button">
						<?php echo esc_html( $atts['button_text'] ); ?>
					</button>

					<!-- Result area -->
					<div id="wlg-result" class="wlg-result" style="display:none;">
						<p class="wlg-result-label">Tu enlace de WhatsApp:</p>
						<div class="wlg-result-row">
							<input type="text" id="wlg-link-output" class="wlg-input wlg-link-input" readonly />
							<button id="wlg-copy-btn" class="wlg-copy-btn" type="button" title="Copiar enlace">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
							</button>
						</div>
						<a id="wlg-open-link" class="wlg-open-link" href="#" target="_blank" rel="noopener noreferrer">
							Abrir en WhatsApp &rarr;
						</a>
					</div>

					<div id="wlg-error" class="wlg-error" style="display:none;"></div>
				</div>

				<!-- Phone mockup side -->
				<div class="wlg-mockup-side">
					<div class="wlg-phone">
						<div class="wlg-phone-header">
							<div class="wlg-phone-avatar"></div>
							<div class="wlg-phone-info">
								<span class="wlg-phone-name">Dolor Lorem</span>
								<span class="wlg-phone-status">online</span>
							</div>
							<div class="wlg-phone-actions">
								<span>&#8942;</span>
							</div>
						</div>
						<div class="wlg-phone-body">
							<div class="wlg-chat-bubble">
								<span id="wlg-preview-text">Aquí aparecerá tu texto personalizado</span>
								<span class="wlg-bubble-time">&#128522;</span>
							</div>
						</div>
					</div>
				</div>

			</div><!-- /.wlg-inner -->
		</div><!-- /.wlg-wrap -->
		<?php
		return ob_get_clean();
	}
}

new WhatsApp_Link_Generator();
