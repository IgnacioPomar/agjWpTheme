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
		$title = get_the_title ($legalPageId);
		echo "<a href=\"$url\">$title</a>";

		$separator = '<span class="separator">|</span>';
	}
	$privacyPageId = get_option ('agj_privacy_page');
	if ($privacyPageId)
	{
		$url = get_permalink ($privacyPageId);
		$title = get_the_title ($privacyPageId);
		echo $separator . "<a href=\"$url\">$title</a>";

		$separator = '<span class="separator">|</span>';
	}
	$cookiePageId = get_option ('agj_cookie_page');
	if ($cookiePageId)
	{
		$url = get_permalink ($cookiePageId);
		$title = get_the_title ($cookiePageId);
		echo $separator . "<a href=\"$url\">$title</a>";
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