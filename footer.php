    </main>
	<div class="contact-footer<?=is_front_page () ? ' frontpage' : ''?>">
	

	<!-- TODO: get email and usr of "aviso Legal" from its page ID -->
	
	<?php
	$separator = '';
	echo '<p class="copyright">';
	$legalPageId = get_option ('agj_legal_page');
	if ($legalPageId)
	{
		$url = get_permalink ($legalPageId);
		echo '<a href="' . $url . '">Aviso</a>';

		$separator = '<span class="separator">|</span>';
	}
	$privacyPageId = get_option ('agj_privacy_page');
	if ($privacyPageId)
	{
		$url = get_permalink ($privacyPageId);
		echo $separator . '<a href="' . $url . '">Po&iacute;tica de privacidad</a>';

		$separator = '<span class="separator">|</span>';
	}
	$cookiePageId = get_option ('agj_cookie_page');
	if ($cookiePageId)
	{
		$url = get_permalink ($cookiePageId);
		echo $separator . '<a href="' . $url . '">Po&iacute;tica de cookies</a>';
	}

	echo '</p>';
	?>
	
	<p>&copy; <?=date ("Y")?> Alternative Experience&reg;. Todos los derechos reservados.</p>
	</div>
	<?php
	wp_footer ();
	?>
</body>
</html>