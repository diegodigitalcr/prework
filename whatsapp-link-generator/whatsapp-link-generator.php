<?php
/**
 * Plugin Name: WhatsApp Link Generator
 * Plugin URI:  https://github.com/diegodigitalcr/whatsapp-link-generator
 * Description: Genera un enlace de WhatsApp con mensaje personalizado. Selector de país, mockup de celular y panel de administración con colores e íconos personalizables.
 * Version:     2.0.0
 * Author:      Diego Digital
 * License:     GPL-2.0-or-later
 * Text Domain: whatsapp-link-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WLG_VERSION', '2.0.0' );
define( 'WLG_DIR', plugin_dir_path( __FILE__ ) );
define( 'WLG_URL', plugin_dir_url( __FILE__ ) );

require_once WLG_DIR . 'includes/countries.php';
require_once WLG_DIR . 'includes/admin-page.php';

class WhatsApp_Link_Generator {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_shortcode( 'whatsapp_link_generator', array( $this, 'render_shortcode' ) );
	}

	public static function get_settings() {
		$defaults = array(
			'color_primary'    => '#25D366',
			'color_btn_text'   => '#ffffff',
			'title'            => 'Generador de Enlace de WhatsApp con Mensaje Personalizado',
			'button_text'      => 'Generar enlace',
			'default_message'  => 'Quiero más información',
			'show_mockup'      => '1',
			'contact_name'     => 'Mi Negocio',
			'default_country'  => '1',
		);
		$saved = get_option( 'wlg_settings', array() );
		return wp_parse_args( $saved, $defaults );
	}

	public function enqueue_assets() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'whatsapp_link_generator' ) ) {
			return;
		}

		wp_enqueue_style( 'wlg-style', WLG_URL . 'assets/css/style.css', array(), WLG_VERSION );

		$s       = self::get_settings();
		$primary = sanitize_hex_color( $s['color_primary'] );
		$btn_txt = sanitize_hex_color( $s['color_btn_text'] );
		wp_add_inline_style(
			'wlg-style',
			":root{--wlg-primary:{$primary};--wlg-btn-text:{$btn_txt};}"
		);

		wp_enqueue_script( 'wlg-script', WLG_URL . 'assets/js/script.js', array(), WLG_VERSION, true );
		wp_localize_script(
			'wlg-script',
			'wlgData',
			array(
				'countries'      => wlg_get_countries(),
				'defaultCountry' => $s['default_country'],
			)
		);
	}

	public function render_shortcode( $atts ) {
		$s    = self::get_settings();
		$atts = shortcode_atts(
			array(
				'title'       => $s['title'],
				'button_text' => $s['button_text'],
			),
			$atts,
			'whatsapp_link_generator'
		);

		$countries = wlg_get_countries();

		ob_start();
		?>
		<div class="wlg-wrap">

			<h2 class="wlg-title"><?php echo esc_html( $atts['title'] ); ?></h2>

			<p class="wlg-notice">
				<strong>📌 IMPORTANTE:</strong> Selecciona tu país y escribe tu número sin el código de país.
			</p>

			<div class="wlg-inner">

				<!-- ── Columna formulario ── -->
				<div class="wlg-form-side">

					<div class="wlg-brand">
						<svg class="wlg-wa-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48">
							<path fill="#25D366" d="M24 4C12.955 4 4 12.955 4 24c0 3.576.948 6.93 2.607 9.823L4 44l10.406-2.573A19.88 19.88 0 0 0 24 44c11.045 0 20-8.955 20-20S35.045 4 24 4z"/>
							<path fill="#fff" d="M35.176 31.68c-.477 1.348-2.37 2.467-3.873 2.792-.516.11-1.19.198-3.456-.742-2.9-1.2-5.357-3.273-7.3-5.845-.884-1.178-1.482-2.52-1.482-3.985 0-1.334.51-2.527 1.35-3.414.384-.413.83-.635 1.258-.635h.885c.38 0 .714.228.857.578l1.24 3.034c.143.35.05.756-.234 1.01l-.664.592c-.313.28-.35.746-.085 1.066 1.06 1.294 2.3 2.338 3.68 3.045.366.19.823.123 1.108-.163l.68-.684c.27-.27.673-.36 1.032-.227l2.912 1.09c.38.142.627.507.627.91v.578z"/>
						</svg>
						<span class="wlg-brand-name">WhatsApp</span>
					</div>

					<!-- Campo teléfono con selector de país -->
					<div class="wlg-field">
						<label>Tu número de teléfono</label>
						<div class="wlg-phone-row">

							<!-- Selector de país -->
							<div class="wlg-country-wrap" id="wlg-country-wrap">
								<button type="button" class="wlg-country-btn" id="wlg-country-btn" aria-expanded="false" aria-haspopup="listbox">
									<span class="wlg-c-flag" id="wlg-c-flag">🌎</span>
									<span class="wlg-c-dial" id="wlg-c-dial">+1</span>
									<svg class="wlg-c-arrow" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
								</button>
								<div class="wlg-country-panel" id="wlg-country-panel" hidden>
									<div class="wlg-country-search-wrap">
										<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
										<input type="text" class="wlg-country-search" id="wlg-country-search" placeholder="Buscar país..." autocomplete="off" />
									</div>
									<ul class="wlg-country-list" id="wlg-country-list" role="listbox">
										<?php foreach ( $countries as $c ) : ?>
										<li class="wlg-country-item"
											role="option"
											data-dial="<?php echo esc_attr( $c['dial'] ); ?>"
											data-name="<?php echo esc_attr( mb_strtolower( $c['name'] ) ); ?>">
											<span class="wlg-ci-flag"><?php echo esc_html( $c['flag'] ); ?></span>
											<span class="wlg-ci-name"><?php echo esc_html( $c['name'] ); ?></span>
											<span class="wlg-ci-dial">+<?php echo esc_html( $c['dial'] ); ?></span>
										</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>

							<!-- Campo número -->
							<input type="tel" id="wlg-number" class="wlg-input wlg-number-input"
								placeholder="743 2331 2342" autocomplete="off" />
						</div>
					</div>

					<!-- Campo mensaje -->
					<div class="wlg-field">
						<label for="wlg-message">Texto del mensaje</label>
						<input type="text" id="wlg-message" class="wlg-input"
							placeholder="<?php echo esc_attr( $s['default_message'] ); ?>" />
					</div>

					<!-- Botón generar -->
					<button id="wlg-btn" class="wlg-button" type="button">
						<?php echo esc_html( $atts['button_text'] ); ?>
					</button>

					<!-- Resultado -->
					<div id="wlg-result" class="wlg-result" style="display:none;">
						<p class="wlg-result-label">Tu enlace de WhatsApp:</p>
						<div class="wlg-result-row">
							<input type="text" id="wlg-link-output" class="wlg-input wlg-link-input" readonly />
							<button id="wlg-copy-btn" class="wlg-copy-btn" type="button" title="Copiar enlace">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
									<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
								</svg>
							</button>
						</div>
						<a id="wlg-open-link" class="wlg-open-link" href="#" target="_blank" rel="noopener noreferrer">
							Abrir en WhatsApp &#8594;
						</a>
					</div>

					<div id="wlg-error" class="wlg-error" style="display:none;"></div>
				</div>

				<!-- ── Columna mockup ── -->
				<?php if ( ! empty( $s['show_mockup'] ) ) : ?>
				<div class="wlg-mockup-side">
					<div class="wlg-device">
						<div class="wlg-device-screen">

							<!-- Barra de estado -->
							<div class="wlg-status-bar">
								<span class="wlg-status-time">9:41</span>
								<div class="wlg-status-icons">
									<svg width="17" height="12" viewBox="0 0 17 12"><rect x="0" y="4" width="3" height="8" rx="1" fill="currentColor" opacity=".4"/><rect x="4.5" y="2.5" width="3" height="9.5" rx="1" fill="currentColor" opacity=".6"/><rect x="9" y="0" width="3" height="12" rx="1" fill="currentColor"/><rect x="13.5" y="2" width="3" height="8" rx="1" fill="currentColor" opacity=".3"/></svg>
									<svg width="24" height="12" viewBox="0 0 24 12"><rect x="0" y="1" width="21" height="10" rx="3" stroke="currentColor" stroke-width="1.5" fill="none"/><rect x="1.5" y="2.5" width="15" height="7" rx="1.5" fill="currentColor"/><rect x="21.5" y="3.5" width="2" height="5" rx="1" fill="currentColor"/></svg>
								</div>
							</div>

							<!-- Header estilo WhatsApp -->
							<div class="wlg-wa-header">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
								<div class="wlg-wa-avatar">
									<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="rgba(255,255,255,.75)"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
								</div>
								<div class="wlg-wa-info">
									<span class="wlg-wa-cname"><?php echo esc_html( $s['contact_name'] ); ?></span>
									<span class="wlg-wa-online">en línea</span>
								</div>
								<div class="wlg-wa-actions">
									<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="rgba(255,255,255,.85)"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.58.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.01L6.6 10.8z"/></svg>
									<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="rgba(255,255,255,.85)"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
								</div>
							</div>

							<!-- Área de chat -->
							<div class="wlg-chat-area">
								<div class="wlg-chat-datepill">Hoy</div>
								<div class="wlg-bubble-wrap">
									<div class="wlg-bubble">
										<span id="wlg-preview-text">Aquí aparecerá tu texto personalizado</span>
										<div class="wlg-bubble-meta">
											<span class="wlg-bubble-time">9:41</span>
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="10" viewBox="0 0 18 10">
												<path d="M12.5 0L7.5 5.2 5 2.7 3.3 4.4l4.2 4.2L14.2 1.7z" fill="#4FC3F7"/>
												<path d="M15.5 0l-5 5.2-1.3-1.2L7.8 5.4l2.7 2.7L17.2 1.7z" fill="#4FC3F7"/>
											</svg>
										</div>
									</div>
								</div>
							</div>

							<!-- Barra de entrada -->
							<div class="wlg-input-bar">
								<div class="wlg-input-mock">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M8 9s.5-2 4-2 4 2 4 2M8 15s1 2 4 2 4-2 4-2M9 9v6M15 9v6"/></svg>
									<span>Mensaje</span>
								</div>
								<button class="wlg-send-btn" type="button" tabindex="-1">
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#fff"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
								</button>
							</div>

						</div><!-- /.wlg-device-screen -->
					</div><!-- /.wlg-device -->
				</div>
				<?php endif; ?>

			</div><!-- /.wlg-inner -->
		</div><!-- /.wlg-wrap -->
		<?php
		return ob_get_clean();
	}
}

WhatsApp_Link_Generator::get_instance();
