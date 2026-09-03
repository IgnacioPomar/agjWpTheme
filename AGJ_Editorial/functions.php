<?php

// Allow featured Image
// https://stackoverflow.com/a/30235452/74785
add_theme_support ('post-thumbnails');
add_theme_support ('title-tag');


function AGJ_WpTheme_enqueue_styles ()
{
	wp_enqueue_style ('AGJ_WpTheme-style', get_stylesheet_uri (), array (), wp_get_theme ()->get ('Version'), 'all');
	wp_enqueue_script ('AGJ_WpTheme-header-scroll', get_template_directory_uri () . '/assets/js/header-scroll.js', array (), wp_get_theme ()->get ('Version'), true);
}
add_action ('wp_enqueue_scripts', 'AGJ_WpTheme_enqueue_styles');


/**
 * Reskin del plugin ZentryGate (Bloques A y B, solo frontend) con la piel
 * editorial del tema. Fichero propio -en vez de sumarse a style.css- para que
 * sea fácil de identificar/retirar sin tocar el resto del tema. Las deps
 * explícitas (en vez de confiar en el orden de registro de hooks) garantizan
 * que se cargue después de la hoja del plugin y de la del propio tema, para
 * poder sobreescribir sus clases sin !important.
 */
function AGJ_WpTheme_enqueue_zentrygate_reskin ()
{
	wp_enqueue_style ('AGJ_WpTheme-zentrygate', get_template_directory_uri () . '/assets/css/zentrygate.css', array ('AGJ_WpTheme-style', 'zentrygate-styles'), wp_get_theme ()->get ('Version'), 'all');
}
add_action ('wp_enqueue_scripts', 'AGJ_WpTheme_enqueue_zentrygate_reskin');


function add_custom_templates ($templates)
{
	$templates ['page-contact-form.php'] = 'Formulario de contacto';
	$templates ['template-ponente.php'] = 'Ponente';
	$templates ['template-zentrygate.php'] = 'ZentriGate Inscription Form';
	$templates ['template-with-descendants.php'] = 'Por defecto con subpáginas';
	return $templates;
}
add_filter ('theme_page_templates', 'add_custom_templates');


function getMnuAnchored ()
{
	$baseUrl = home_url ();
	$subpages = get_pages (array ('parent' => 0, 'sort_column' => 'menu_order'));
	$retval = array ();
	$first = true;

	foreach ($subpages as $page)
	{
		// Skip the -1 pages: in this theme ar "independent pages"
		if ($page->menu_order > 100 || $page->menu_order < 0) continue;

		if ($first)
		{
			// If the name is hero or home, we skip it: the first link is the home page
			if (! in_array ($page->post_name, array ('hero', 'home')))
			{
				$retval [] = [ "$baseUrl/#{$page->post_name}", esc_html ($page->post_title)];
			}

			$first = false;
		}
		else
		{
			$retval [] = [ "$baseUrl/#{$page->post_name}", esc_html ($page->post_title)];
		}
	}

	return $retval;
}


function habilitar_excerpt_para_paginas ()
{
	add_post_type_support ('page', 'excerpt');
	add_post_type_support ('page', 'custom-fields');
}
add_action ('init', 'habilitar_excerpt_para_paginas');

// ----------- Disable emojis in WordPress ------------
add_action ('init', 'smartwp_disable_emojis');


function smartwp_disable_emojis ()
{
	remove_action ('wp_head', 'print_emoji_detection_script', 7);
	remove_action ('admin_print_scripts', 'print_emoji_detection_script');
	remove_action ('wp_print_styles', 'print_emoji_styles');
	remove_filter ('the_content_feed', 'wp_staticize_emoji');
	remove_action ('admin_print_styles', 'print_emoji_styles');
	remove_filter ('comment_text_rss', 'wp_staticize_emoji');
	remove_filter ('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter ('tiny_mce_plugins', 'disable_emojis_tinymce');
}


function disable_emojis_tinymce ($plugins)
{
	if (is_array ($plugins))
	{
		return array_diff ($plugins, array ('wpemoji'));
	}
	else
	{
		return array ();
	}
}


// ----------- Disable gutenberg_styles ------------
function remove_gutenberg_styles_for_guests ()
{
	// Check if the user is not logged in
	if (! is_admin () && ! is_user_logged_in ())
	{
		// Remove Gutenberg block library CSS
		wp_dequeue_style ('wp-block-library');
		wp_dequeue_style ('wp-block-library-theme');
		wp_dequeue_style ('wc-block-style'); // If using WooCommerce
		wp_dequeue_style ('global-styles'); // For WordPress 5.9+ global styles
	}
}
add_action ('wp_enqueue_scripts', 'remove_gutenberg_styles_for_guests', 100);

// ----------- Theme personalized fields ------------
// Registrar el menú en Ajustes
add_action ('admin_menu', function ()
{
	add_options_page ('Ajustes del tema', // Título de la página
	'Theme Settings', // Nombre del menú
	'manage_options', // Capacidad necesaria
	'settings_agj', // Slug del menú
	'show_settings_agj' // Función de callback
	);
});

// Registrar el campo de configuración
add_action ('admin_init', function ()
{
	// Registramos las opciones del tema
	register_setting ('settings_agj', 'agj_legal_page');
	register_setting ('settings_agj', 'agj_privacy_page');
	register_setting ('settings_agj', 'agj_cookie_page');

	register_setting ('settings_agj', 'agj_main_phone', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '']);
	register_setting ('settings_agj', 'agj_email', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_email', 'default' => '']);
	register_setting ('settings_agj', 'agj_logo_id', [ 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0]);

	add_settings_section ('section_agj_footer', 'Footer Configuration', null, 'settings_agj');

	// añadimos los controladores del tema
	add_settings_field ('agj_legal_page', 'Página de aviso Legal', function ()
	{
		$selected = get_option ('agj_legal_page');
		wp_dropdown_pages ([ 'name' => 'agj_legal_page', 'selected' => $selected, 'show_option_none' => '— Selecciona una página —', 'option_none_value' => '']);
	}, 'settings_agj', 'section_agj_footer');

	add_settings_field ('agj_privacy_page', 'Página de politica de privacidad', function ()
	{
		$selected = get_option ('agj_privacy_page');
		wp_dropdown_pages ([ 'name' => 'agj_privacy_page', 'selected' => $selected, 'show_option_none' => '— Selecciona una página —', 'option_none_value' => '']);
	}, 'settings_agj', 'section_agj_footer');

	add_settings_field ('agj_cookie_page', 'Página de politica de cookies', function ()
	{
		$selected = get_option ('agj_cookie_page');
		wp_dropdown_pages ([ 'name' => 'agj_cookie_page', 'selected' => $selected, 'show_option_none' => '— Selecciona una página —', 'option_none_value' => '']);
	}, 'settings_agj', 'section_agj_footer');

	add_settings_field ('agj_main_phone', 'Teléfono de contacto', function ()
	{
		$val = get_option ('agj_main_phone', '');
		printf ('<input type="tel" id="agj_main_phone" name="agj_main_phone" value="%s" class="regular-text">', esc_attr ($val));
	}, 'settings_agj', 'section_agj_footer');

	add_settings_field ('agj_email', 'Email de contacto', function ()
	{
		$val = get_option ('agj_email', '');
		printf ('<input type="email" id="agj_email" name="agj_email" value="%s" class="regular-text" >', esc_attr ($val));
	}, 'settings_agj', 'section_agj_footer');

	add_settings_field ('agj_logo_id', 'Logo del sitio', function ()
	{
		$logo_id = get_option ('agj_logo_id', 0);
		$image_url = $logo_id ? wp_get_attachment_image_url ($logo_id, 'medium') : '';
		echo '<div>';
		echo '<img id="agj-logo-preview" src="' . esc_url ($image_url) . '" style="max-height:100px; display:' . ($image_url ? 'block' : 'none') . '; margin-bottom:10px;">';
		echo '<input type="hidden" id="agj_logo_id" name="agj_logo_id" value="' . esc_attr ($logo_id) . '">';
		echo '<button type="button" class="button" id="agj-select-logo">Seleccionar logo</button>';
		echo '<button type="button" class="button" id="agj-remove-logo" style="margin-left:10px;">Eliminar</button>';
		echo '</div>';
	}, 'settings_agj', 'section_agj_footer');
});


// Mostrar la página HTML de ajustes
function show_settings_agj ()
{
	?>
    <div class="wrap">
        <h1>AGJ Theme Settings</h1>
        <form method="post" action="options.php">
            <?php
	settings_fields ('settings_agj');
	do_settings_sections ('settings_agj');
	submit_button ();
	?>
        </form>
    </div>
    <?php
}

add_action ('admin_enqueue_scripts', function ($hook)
{
	// Solo cargar en la página de opciones
	if ($hook !== 'settings_page_settings_agj') return;

	wp_enqueue_media ();
	wp_add_inline_script ('jquery', <<<JS
	jQuery(document).ready(function($){
		let frame;
				
		$('#agj-select-logo').on('click', function(e){
			e.preventDefault();
			if (frame) frame.open();
			else {
				frame = wp.media({
					title: 'Seleccionar Logo',
					button: { text: 'Usar esta imagen' },
					multiple: false
				});
				frame.on('select', function(){
					const attachment = frame.state().get('selection').first().toJSON();
					$('#agj_logo_id').val(attachment.id);
					$('#agj-logo-preview').attr('src', attachment.url).show();
				});
				frame.open();
			}
		});
				
		$('#agj-remove-logo').on('click', function(){
			$('#agj_logo_id').val('');
			$('#agj-logo-preview').hide().attr('src', '');
		});
	});
	JS);
});

// --------------------------------------------------------------------------------------------------------------
// ------------------------------------------- Suctom fields -------------------------------------------
// --------------------------------------------------------------------------------------------------------------

/*
 * Enable custom fields in the block editor for pages
 * This allows the use of custom fields in the block editor for pages.
 * It is optional, but it is useful if you want to use custom fields in the block editor.
 */
add_filter ('block_editor_settings_all', function ($settings, $editor_context)
{
	$post = isset ($editor_context->post) ? $editor_context->post : null;

	if ($post && $post->post_type === 'page' && 
	// comprobar plantilla activa
	get_page_template_slug ($post->ID) === 'template-ponente.php')
	{
		$settings ['enableCustomFields'] = true;
	}

	return $settings;
}, 10, 2);

add_action ('add_meta_boxes', function ()
{
	global $post;

	// Salir si no es página o si no usa template-ponente.php
	if (! $post || $post->post_type !== 'page' || get_page_template_slug ($post->ID) !== 'template-ponente.php')
	{
		return;
	}

	// Tus dos filtros (claves) y valor por defecto
	$defaults = [ 'Cargo' => '']; // , 'nombre_completo' => ''

	foreach ($defaults as $key => $value)
	{
		if (! metadata_exists ('post', $post->ID, $key))
		{
			add_post_meta ($post->ID, $key, $value, false);
		}
	}
});


// --------------------------------------------------------------------------------------------------------------
// ------------------------------------------- Theme custom functions -------------------------------------------
// --------------------------------------------------------------------------------------------------------------

/**
 * Show the subpages of the current page
 *
 * @param array $subpages
 *        	Array of subpages to show
 * @param string $class
 *        	Additional class for the container
 */
function showSubpages (&$subpages, $class = "")
{
	if ($subpages)
	{
		echo '<div class="subpages ' . $class . '">';
		foreach ($subpages as $subpage)
		{
			// Skip the -1 pages: in this theme ar "independent pages"
			if ($subpage->menu_order > 100 || $subpage->menu_order < 0) continue;

			// sp comes from Sub Page
			$spId = $subpage->post_name;

			// Show the current page
			$template_slug = get_page_template_slug ($subpage->ID);
			if ($template_slug)
			{

				$template = locate_template ($template_slug);
				if ($template)
				{
					$GLOBALS ['currentPage'] = &$subpage;
					include ($template);
				}
				else if ('template-zentrygate.php' == $template_slug)
				{

					$template = ZENTRYGATE_DIR . 'templates/template-zentrygate.php';
					if (file_exists ($template))
					{
						$GLOBALS ['currentPage'] = &$subpage;
						include ($template);
					}
					else
						echo "<div class=\"container\" id=\"$spId\">Template not found: $template</div>";
				}
				else
				{
					echo "<div class=\"container\" id=\"$spId\">Template not found: $template_slug</div>";
				}
			}
			else
			{
				echo "<div class=\"container\" id=\"$spId\">";

				// Si la subpágina tiene una imagen destacada, va junto al
				// contenido dentro de un wrapper propio: así el ".container"
				// exterior sigue a ancho completo (línea/fondo de sección) y
				// solo ese wrapper interior queda centrado a --container-max-width,
				// igual que ".content" hace por sí solo cuando no hay imagen.
				$hasThumbnail = has_post_thumbnail ($subpage->ID);
				if ($hasThumbnail)
				{
					echo '<div class="container-inner">';
					echo '<div class="featured-image">' . get_the_post_thumbnail ($subpage->ID, 'full') . '</div>';
				}

				$content = apply_filters ('the_content', $subpage->post_content);
				$content = decorateSubpageContent ($spId, $content, $subpage->post_title);

				echo '<div class="content">';
				echo $content;
				echo '</div>';

				if ($hasThumbnail) echo '</div>';

				echo '</div>';
			}
		}
		echo '</div>';
	}
}


/**
 * Añade maquetación exclusiva de AGJ_Editorial a ciertas secciones de la portada,
 * sin modificar el contenido real de la página (que sigue siendo válido para
 * cualquier otro tema). Hoy cubre:
 * - "programa": pestañas Jornada 1 / Jornada 2 (solo CSS, sin JS).
 * - "inscripcion": tarjeta de estado, hueco donde se montará el formulario real
 *   de ZentryGate (template-zentrygate.php) cuando se abra la inscripción.
 *
 * @param string $spId
 *        	Slug de la subpágina (post_name)
 * @param string $content
 *        	Contenido ya filtrado (the_content) de la subpágina
 * @param string $postTitle
 *        	Título de la página (post_title) — es el mismo texto que ya se usa
 *        	para el enlace del menú, y es lo que se usa como antetítulo rojo
 * @return string
 */
function decorateSubpageContent ($spId, $content, $postTitle = '')
{
	// Antetítulo rojo ("Información", "Programa", …) sobre el título de la sección.
	// Usa el post_title de la página (el mismo texto del menú), no el <h1> del
	// contenido: en "informacion", por ejemplo, el h1 del contenido es
	// "Abogacía y Gestión Pública", distinto del título/antetítulo de la sección.
	$eyebrowSections = array ('informacion', 'programa', 'cuando-y-donde', 'inscripcion');
	if (in_array ($spId, $eyebrowSections) && $postTitle)
	{
		$content = '<p class="section-eyebrow">' . esc_html ($postTitle) . '</p>' . $content;
	}

	if ($spId === 'informacion')
	{
		// Envuelve antetítulo+título en una columna y los párrafos en otra,
		// para el layout de 2 columnas del diseño (título fijo a la izquierda).
		// Es maquetación pura: no cambia ni una palabra del contenido.
		if (preg_match ('/^(.*<h1[^>]*>.*?<\/h1>)(.*)$/s', $content, $m))
		{
			$content = '<div class="info-head">' . $m [1] . '</div><div class="info-body">' . $m [2] . '</div>';
		}
	}
	else if ($spId === 'programa')
	{
		// Identifica cada bloque .jornada del contenido real por su propio id
		// (se lo asigna si no lo tiene) y saca el texto de su pestaña de su
		// propio <h2>, para que la pestaña nunca se desincronice del contenido
		// ni dependa de la posición (nada de :nth-of-type).
		$jornadas = array ();
		$index = 0;
		$content = preg_replace_callback (
			'/<div([^>]*\bclass="[^"]*\bjornada\b[^"]*"[^>]*)>(.*?)<\/div>/s',
			function ($m) use (&$jornadas, &$index)
			{
				$index++;
				$attrs = $m [1];
				$inner = $m [2];

				$id = '';
				if (preg_match ('/\bid="([^"]+)"/', $attrs, $idMatch))
				{
					$id = preg_replace ('/[^A-Za-z0-9_-]/', '', $idMatch [1]);
				}
				if ($id === '')
				{
					$id = 'jornada-' . $index;
					$attrs .= ' id="' . $id . '"';
				}

				$label = $id;
				if (preg_match ('/<h2[^>]*>(.*?)<\/h2>/s', $inner, $h2Match))
				{
					$label = trim (wp_strip_all_tags ($h2Match [1]));
				}

				$jornadas [] = array ('id' => $id, 'label' => $label);

				return '<div' . $attrs . '>' . $inner . '</div>';
			},
			$content
		);

		$tabs = '';
		if (count ($jornadas) >= 2)
		{
			// Los radios deben quedar como hermanos directos de los bloques
			// .jornada (no anidados en .programa-head/.jornada-tabs) para que el
			// selector CSS ":checked ~ #id" pueda alcanzarlos con el combinador
			// de hermanos; el <label for="..."> sí puede vivir en otro sitio.
			$radios = '';
			foreach ($jornadas as $i => $jornada)
			{
				$checked = $i === 0 ? ' checked' : '';
				$radios .= '<input type="radio" name="jornada-tab" id="tab-' . esc_attr ($jornada ['id']) . '" class="jornada-tab-input"' . $checked . '>';
			}

			$tabs = '<div class="jornada-tabs">';
			foreach ($jornadas as $jornada)
			{
				$tabs .= '<label for="tab-' . esc_attr ($jornada ['id']) . '" class="jornada-tab-btn">' . esc_html ($jornada ['label']) . '</label>';
			}
			$tabs .= '</div>';

			// Al marcar la pestaña de una jornada, oculta el resto de jornadas
			// (emparejadas por id, no por posición en el contenido) y resalta
			// su propia pestaña. El label vive dentro de .programa-head, no
			// como hermano directo del radio, así que hace falta el descendiente
			// ".programa-head label[for=...]" en vez de un simple "~ label".
			$hideRules = array ();
			$activeRules = array ();
			foreach ($jornadas as $selected)
			{
				$activeRules [] = '#tab-' . $selected ['id'] . ':checked ~ .programa-head label[for="tab-' . $selected ['id'] . '"]';
				foreach ($jornadas as $other)
				{
					if ($other ['id'] !== $selected ['id'])
					{
						$hideRules [] = '#tab-' . $selected ['id'] . ':checked ~ #' . $other ['id'];
					}
				}
			}
			if ($hideRules) $tabs .= '<style>' . implode (",\n", $hideRules) . ' { display: none; }</style>';
			if ($activeRules) $tabs .= '<style>' . implode (",\n", $activeRules) . ' { color: var(--agj-ink); font-weight: 600; border-bottom-color: var(--agj-red); }</style>';

			// El antetítulo+título de la sección y las pestañas quedan juntos en
			// una misma fila (título a la izquierda, pestañas a la derecha); los
			// radios van sueltos delante para seguir siendo hermanos de .jornada.
			$withHead = preg_replace ('/^((?:<p class="section-eyebrow">.*?<\/p>\s*)?<h1[^>]*>.*?<\/h1>)/s', $radios . '<div class="programa-head"><div class="programa-head-title">$1</div>' . $tabs . '</div>', $content, 1);
			if ($withHead !== null) $content = $withHead;
		}
	}
	else if ($spId === 'cuando-y-donde')
	{
		// Separa el bloque de texto (antetítulo+título+párrafos+cajas) del mapa
		// en dos columnas, sin cambiar el contenido de la página.
		if (preg_match ('/^(.*?)(<div class="mapouter".*)$/s', $content, $m))
		{
			$mapPart = preg_replace ('/<p>\s*<\/p>\s*$/', '', $m [2]);
			$content = '<div class="cyd-text">' . $m [1] . '</div><div class="cyd-map">' . $mapPart . '</div>';
		}
	}
	else if ($spId === 'inscripcion')
	{
		// Maqueta de "inscripción pendiente de apertura", desactivada: no encaja
		// con el contenido real actual de la página (que ya indica que el plazo
		// está cerrado). Reactivar cuando esta página use template-zentrygate.php
		// y este hueco vaya a ocuparlo el formulario real.
		/*
		$content .= '<div class="inscripcion-card">'
			. '<div class="inscripcion-card-status"><span class="inscripcion-dot"></span>Inscripción pendiente de apertura</div>'
			. '<p class="inscripcion-card-text">Deje su correo y le avisaremos en cuanto se abra el plazo.</p>'
			. '<form class="inscripcion-card-form" onsubmit="return false;">'
			. '<input type="email" placeholder="correo@ejemplo.es" disabled>'
			. '<button type="button" disabled>Avisadme</button>'
			. '</form>'
			. '</div>';
		*/
	}

	return $content;
}


/**
 * Function exclusive for template-team.php.
 * Format the team member information.
 *
 * @param int $id
 *        	Post ID of the team member
 * @param string $postTitle
 *        	Title of the post
 * @param string $postName
 *        	Name of the post (Ponente name)
 * @param string $content
 *        	Content of the post
 */
function formatPonente ($id, $postTitle, $postName, $content)
{
	$cargo = esc_html (get_post_meta ($id, 'Cargo', true) ?: '');
	$imgUrl = get_the_post_thumbnail_url ($id, 'large');

	echo '<div id="' . $postName . '" class="ponente">';
	// Columna izquierda: foto + nombre + cargo
	echo '<div class="ponente-info-left">';
	if ($imgUrl)
	{
		echo '<picture><img class="ponente-info-image" src="' . esc_url ($imgUrl) . '" alt="' . esc_attr ($postTitle) . '"></picture>';
	}
	echo '<h3 class="name">' . esc_html ($postTitle) . '</h3>';
	if ($cargo) echo '<p class="ponente-cargo">' . $cargo . '</p>';
	echo '</div>';

	// Columna derecha: biografía
	echo '<div class="ponente-info-right">';
	echo apply_filters ('the_content', $content);
	echo '</div>';
	echo '</div>';
}
