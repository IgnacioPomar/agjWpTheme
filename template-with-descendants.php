<?php
/*
 * Template Name: Plantilla con Campos Personalizados
 */
if (isset ($GLOBALS ['currentPage']))
{
	$page = &$GLOBALS ['currentPage'];

	echo "<div class=\"container\" id=\"$page->post_name\">";

	// Si la subpágina tiene una imagen destacada, mostrarla
	if (has_post_thumbnail ($page->ID))
	{
		echo '<div class="featured-image">' . get_the_post_thumbnail ($page->ID, 'full') . '</div>';
	}

	echo '<div class="content">';
	echo apply_filters ('the_content', $page->post_content);
	echo '</div></div>';

	$subSubpages = get_pages (array ('parent' => $page->ID, 'sort_column' => 'menu_order'));
	if ($subSubpages)
	{
		showSubpages ($subSubpages, $page->post_name);
	}
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

			$pageID = get_the_ID ();
			$pageName = get_post_field ('post_name');

			echo "<div class=\"container\" id=\"$pageName\">";

			// Si la subpágina tiene una imagen destacada, mostrarla
			if (has_post_thumbnail ($pageID))
			{
				echo '<div class="featured-image">' . get_the_post_thumbnail ($pageID, 'full') . '</div>';
			}

			echo '<div class="content">';
			echo apply_filters ('the_content', get_the_content ());
			echo '</div></div>';

			$subSubpages = get_pages (array ('parent' => $pageID, 'sort_column' => 'menu_order'));
			if ($subSubpages)
			{
				showSubpages ($subSubpages, $pageName);
			}
		}
	}
	else
	{
		// Mensaje en caso de que no haya contenido
		echo '<p>No se encontró contenido en esta página.</p>';
	}
	get_footer ();
}
