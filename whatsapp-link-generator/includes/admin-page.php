<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Hooks ── */
add_action( 'admin_menu',       'wlg_add_admin_menu' );
add_action( 'admin_init',       'wlg_register_settings' );
add_action( 'admin_enqueue_scripts', 'wlg_admin_enqueue' );

/* ── Menú ── */
function wlg_add_admin_menu() {
	add_options_page(
		'WhatsApp Link Generator',
		'WhatsApp Generator',
		'manage_options',
		'wlg-settings',
		'wlg_render_settings_page'
	);
}

/* ── Enqueue admin assets ── */
function wlg_admin_enqueue( $hook ) {
	if ( 'settings_page_wlg-settings' !== $hook ) return;
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'wlg-admin-style', WLG_URL . 'assets/css/admin.css', array(), WLG_VERSION );
	wp_enqueue_script( 'wlg-admin-script', WLG_URL . 'assets/js/admin.js', array( 'wp-color-picker' ), WLG_VERSION, true );
}

/* ── Registro de opciones ── */
function wlg_register_settings() {
	register_setting( 'wlg_options_group', 'wlg_settings', array( 'sanitize_callback' => 'wlg_sanitize' ) );

	/* Colores */
	add_settings_section( 'wlg_sec_colors', '🎨 Colores', '__return_false', 'wlg-settings' );
	add_settings_field( 'color_primary',  'Color primario',         'wlg_field_color_primary',  'wlg-settings', 'wlg_sec_colors' );
	add_settings_field( 'color_btn_text', 'Color texto del botón',  'wlg_field_color_btn_text', 'wlg-settings', 'wlg_sec_colors' );

	/* Textos */
	add_settings_section( 'wlg_sec_texts', '📝 Textos', '__return_false', 'wlg-settings' );
	add_settings_field( 'title',           'Título del generador',   'wlg_field_title',           'wlg-settings', 'wlg_sec_texts' );
	add_settings_field( 'button_text',     'Texto del botón',        'wlg_field_button_text',     'wlg-settings', 'wlg_sec_texts' );
	add_settings_field( 'default_message', 'Mensaje por defecto',    'wlg_field_default_message', 'wlg-settings', 'wlg_sec_texts' );

	/* Mockup */
	add_settings_section( 'wlg_sec_mockup', '📱 Mockup del celular', '__return_false', 'wlg-settings' );
	add_settings_field( 'show_mockup',     'Mostrar mockup',         'wlg_field_show_mockup',     'wlg-settings', 'wlg_sec_mockup' );
	add_settings_field( 'contact_name',    'Nombre del contacto',    'wlg_field_contact_name',    'wlg-settings', 'wlg_sec_mockup' );

	/* País */
	add_settings_section( 'wlg_sec_country', '🌎 País por defecto', '__return_false', 'wlg-settings' );
	add_settings_field( 'default_country', 'País predeterminado',   'wlg_field_default_country', 'wlg-settings', 'wlg_sec_country' );
}

/* ── Sanitización ── */
function wlg_sanitize( $input ) {
	$clean = array();
	$clean['color_primary']   = isset( $input['color_primary'] )   ? sanitize_hex_color( $input['color_primary'] )   : '#25D366';
	$clean['color_btn_text']  = isset( $input['color_btn_text'] )  ? sanitize_hex_color( $input['color_btn_text'] )  : '#ffffff';
	$clean['title']           = isset( $input['title'] )           ? sanitize_text_field( $input['title'] )           : '';
	$clean['button_text']     = isset( $input['button_text'] )     ? sanitize_text_field( $input['button_text'] )     : '';
	$clean['default_message'] = isset( $input['default_message'] ) ? sanitize_text_field( $input['default_message'] ) : '';
	$clean['show_mockup']     = ! empty( $input['show_mockup'] ) ? '1' : '0';
	$clean['contact_name']    = isset( $input['contact_name'] )    ? sanitize_text_field( $input['contact_name'] )    : '';
	$clean['default_country'] = isset( $input['default_country'] ) ? sanitize_text_field( $input['default_country'] ) : '1';
	return $clean;
}

/* ── Helpers de campo ── */
function wlg_get( $key ) {
	$s = WhatsApp_Link_Generator::get_settings();
	return $s[ $key ] ?? '';
}

function wlg_field_color_primary() {
	$v = esc_attr( wlg_get( 'color_primary' ) );
	echo "<input type='text' name='wlg_settings[color_primary]' value='{$v}' class='wlg-color-picker' data-default-color='#25D366' />";
	echo "<p class='description'>Afecta el botón, bordes al hacer foco y los enlaces.</p>";
}

function wlg_field_color_btn_text() {
	$v = esc_attr( wlg_get( 'color_btn_text' ) );
	echo "<input type='text' name='wlg_settings[color_btn_text]' value='{$v}' class='wlg-color-picker' data-default-color='#ffffff' />";
}

function wlg_field_title() {
	$v = esc_attr( wlg_get( 'title' ) );
	echo "<input type='text' name='wlg_settings[title]' value='{$v}' class='regular-text' />";
}

function wlg_field_button_text() {
	$v = esc_attr( wlg_get( 'button_text' ) );
	echo "<input type='text' name='wlg_settings[button_text]' value='{$v}' class='regular-text' />";
}

function wlg_field_default_message() {
	$v = esc_attr( wlg_get( 'default_message' ) );
	echo "<input type='text' name='wlg_settings[default_message]' value='{$v}' class='regular-text' />";
	echo "<p class='description'>Texto que aparece como placeholder en el campo de mensaje.</p>";
}

function wlg_field_show_mockup() {
	$checked = checked( '1', wlg_get( 'show_mockup' ), false );
	echo "<label><input type='checkbox' name='wlg_settings[show_mockup]' value='1' {$checked} /> Mostrar el mockup de celular junto al formulario</label>";
}

function wlg_field_contact_name() {
	$v = esc_attr( wlg_get( 'contact_name' ) );
	echo "<input type='text' name='wlg_settings[contact_name]' value='{$v}' class='regular-text' />";
	echo "<p class='description'>Nombre que aparece en la cabecera del mockup de WhatsApp.</p>";
}

function wlg_field_default_country() {
	$current   = wlg_get( 'default_country' );
	$countries = wlg_get_countries();
	echo "<select name='wlg_settings[default_country]' class='regular-text'>";
	foreach ( $countries as $c ) {
		$selected = selected( $current, $c['dial'], false );
		$label    = esc_html( $c['flag'] . ' ' . $c['name'] . ' (+' . $c['dial'] . ')' );
		echo "<option value='" . esc_attr( $c['dial'] ) . "' {$selected}>{$label}</option>";
	}
	echo "</select>";
	echo "<p class='description'>País que aparece seleccionado por defecto en el formulario.</p>";
}

/* ── Página de ajustes ── */
function wlg_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$s = WhatsApp_Link_Generator::get_settings();
	?>
	<div class="wrap wlg-admin-wrap">

		<div class="wlg-admin-header">
			<div class="wlg-admin-logo">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="36" height="36">
					<path fill="#25D366" d="M24 4C12.955 4 4 12.955 4 24c0 3.576.948 6.93 2.607 9.823L4 44l10.406-2.573A19.88 19.88 0 0 0 24 44c11.045 0 20-8.955 20-20S35.045 4 24 4z"/>
					<path fill="#fff" d="M35.176 31.68c-.477 1.348-2.37 2.467-3.873 2.792-.516.11-1.19.198-3.456-.742-2.9-1.2-5.357-3.273-7.3-5.845-.884-1.178-1.482-2.52-1.482-3.985 0-1.334.51-2.527 1.35-3.414.384-.413.83-.635 1.258-.635h.885c.38 0 .714.228.857.578l1.24 3.034c.143.35.05.756-.234 1.01l-.664.592c-.313.28-.35.746-.085 1.066 1.06 1.294 2.3 2.338 3.68 3.045.366.19.823.123 1.108-.163l.68-.684c.27-.27.673-.36 1.032-.227l2.912 1.09c.38.142.627.507.627.91v.578z"/>
				</svg>
				<h1>WhatsApp Link Generator</h1>
			</div>
			<span class="wlg-admin-version">v<?php echo esc_html( WLG_VERSION ); ?></span>
		</div>

		<div class="wlg-admin-body">

			<div class="wlg-admin-main">
				<form method="post" action="options.php">
					<?php settings_fields( 'wlg_options_group' ); ?>

					<?php
					$sections = array(
						'wlg_sec_colors'  => array( 'icon' => '🎨', 'label' => 'Colores' ),
						'wlg_sec_texts'   => array( 'icon' => '📝', 'label' => 'Textos' ),
						'wlg_sec_mockup'  => array( 'icon' => '📱', 'label' => 'Mockup del celular' ),
						'wlg_sec_country' => array( 'icon' => '🌎', 'label' => 'País por defecto' ),
					);
					?>

					<?php foreach ( $sections as $sec_id => $sec_meta ) : ?>
					<div class="wlg-card">
						<h2><?php echo esc_html( $sec_meta['icon'] . ' ' . $sec_meta['label'] ); ?></h2>
						<table class="form-table" role="presentation">
							<?php do_settings_fields( 'wlg-settings', $sec_id ); ?>
						</table>
					</div>
					<?php endforeach; ?>

					<div class="wlg-card wlg-card-shortcode">
						<h2>⚡ Uso del shortcode</h2>
						<p>Copia este shortcode y pégalo en cualquier página o entrada de WordPress:</p>
						<code class="wlg-shortcode">[whatsapp_link_generator]</code>
						<p class="description">También puedes sobreescribir el título o el botón directamente en el shortcode:</p>
						<code class="wlg-shortcode">[whatsapp_link_generator title="Mi generador" button_text="Crear enlace"]</code>
					</div>

					<?php submit_button( 'Guardar cambios', 'primary wlg-save-btn' ); ?>
				</form>
			</div>

			<!-- Preview lateral -->
			<div class="wlg-admin-sidebar">
				<div class="wlg-preview-card">
					<h3>Vista previa del botón</h3>
					<button class="wlg-preview-btn" id="wlg-preview-btn"
						style="background:<?php echo esc_attr( $s['color_primary'] ); ?>;color:<?php echo esc_attr( $s['color_btn_text'] ); ?>">
						<?php echo esc_html( $s['button_text'] ); ?>
					</button>
				</div>
				<div class="wlg-preview-card">
					<h3>Shortcode</h3>
					<code>[whatsapp_link_generator]</code>
					<p class="description" style="margin-top:8px">Agrégalo a cualquier página con el editor de WordPress o con un bloque Shortcode.</p>
				</div>
			</div>

		</div><!-- /.wlg-admin-body -->
	</div>
	<?php
}
