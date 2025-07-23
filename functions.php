<?php

// Allow featured Image
// https://stackoverflow.com/a/30235452/74785
add_theme_support ('post-thumbnails');
add_theme_support ('title-tag');


function AGJ_WpTheme_enqueue_styles ()
{
	wp_enqueue_style ('AGJ_WpTheme-style', get_stylesheet_uri (), array (), wp_get_theme ()->get ('Version'), 'all');
}
add_action ('wp_enqueue_scripts', 'AGJ_WpTheme_enqueue_styles');


function add_custom_templates ($templates)
{
	$templates ['page-contact-form.php'] = 'Formulario de contacto';
	$templates ['template-ponente.php'] = 'Ponente';
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

					$template = ZENTRYGATE_PLUGIN_DIR . 'templates/template-zentrygate.php';
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

				// Si la subpágina tiene una imagen destacada, mostrarla
				if (has_post_thumbnail ($subpage->ID))
				{
					echo '<div class="featured-image">' . get_the_post_thumbnail ($subpage->ID, 'full') . '</div>';
				}

				echo '<div class="content">';
				echo apply_filters ('the_content', $subpage->post_content);
				echo '</div></div>';
			}
		}
		echo '</div>';
	}
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
