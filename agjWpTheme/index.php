<?php
get_header ();
if (have_posts ())
{

	while (have_posts ())
	{
		the_post ();

		$containerID = get_post_meta (get_the_ID (), 'containerID', true) ?: get_post_field ('post_name');

		echo '<div class="container" id="' . $containerID . '">';
		echo '<div class="content">' . apply_filters ('the_content', get_the_content ()) . '</div>';
		echo '</div>';
	}
}
get_footer ();
// get_sidebar();

