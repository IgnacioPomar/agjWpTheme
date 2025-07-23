<?php
/*
 * Template Name: Plantilla con Campos Personalizados
 */
if (isset ($GLOBALS ['currentPage']))
{
	$page = &$GLOBALS ['currentPage'];

	formatTeam ($page->ID, $page->post_title, $page->post_name, $page->post_content);
}
else
{
	get_header ();
	if (have_posts ())
	{
		// This MUST be a page, so, it'll be only once, we dont need to iterate
		// while (have_posts ())
		{
			the_post ();
			formatTeam (get_the_ID (), get_the_title (), get_post_field ('post_name'), get_the_content ());
		}
	}
	else
	{
		// Mensaje en caso de que no haya contenido
		echo '<p>No se encontró contenido en esta página.</p>';
	}
	get_footer ();
}



