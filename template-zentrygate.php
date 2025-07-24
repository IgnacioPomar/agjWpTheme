<?php
/*
 * Template Name: Zentrygate Inscription Form
 * Description: Shows the page content and appends the plugin form.
 */
require_once ZENTRYGATE_PLUGIN_DIR . 'ZentryGate/Auth.php';
require_once ZENTRYGATE_PLUGIN_DIR . 'ZentryGate/PageRenderer.php';

use ZentryGate\PageRenderer;
if (isset ($GLOBALS ['currentPage']))
{
	// Estamos en el Frontpage (modo embebido), insertamos el iframe hacia esta misma página
	$page = &$GLOBALS ['currentPage'];

	echo '<div class="container" id="' . $page->post_name . '">';
	echo '<div class="content">';

	$url = get_permalink ($page->ID);
	echo '<iframe src="' . esc_url ($url) . '" style="width:100%; height:600px; border:none;" loading="lazy" title="Formulario de inscripción"></iframe>';

	echo '</div></div>';
}
else
{
	// Carga directa (por iframe), renderizamos el plugin directamente sin header/footer
	// ob_start ();

	$title = '';
	if (have_posts ())
	{
		// This MUST be a page, so, it'll be only once, we dont need to iterate
		// while (have_posts ())
		{
			the_post ();
			$title = get_the_title ();
		}
	}

	$renderer = new PageRenderer ();
	$renderer->echoHeader ($title);
	$renderer->renderPluginPageContents ();
	$renderer->echoFooter ();
}

