    </main>
	<div class="contact-footer<?=is_front_page () ? ' frontpage' : ''?>">
	<?php
	$logo_id = get_option ('agj_logo_id');
	$logo_url = $logo_id ? wp_get_attachment_image_url ($logo_id, 'full') : '';
	?>
	<div class="footer-top">
		<div class="footer-brand">
			<img src="<?=esc_url ($logo_url);?>" alt="Logo" class="site-logo">
			<span class="logoTxt">Abogacía General de la Comunidad de Madrid</span>
		</div>

		<!-- TODO: get email and usr of "aviso Legal" from its page ID -->

		<?php
		echo '<p class="copyright">';
		$legalPageId = get_option ('agj_legal_page');
		if ($legalPageId)
		{
			$url = get_permalink ($legalPageId);
			$title = get_the_title ($legalPageId);
			echo "<a href=\"$url\">$title</a>";
		}
		$privacyPageId = get_option ('agj_privacy_page');
		if ($privacyPageId)
		{
			$url = get_permalink ($privacyPageId);
			$title = get_the_title ($privacyPageId);
			echo "<a href=\"$url\">$title</a>";
		}
		$cookiePageId = get_option ('agj_cookie_page');
		if ($cookiePageId)
		{
			$url = get_permalink ($cookiePageId);
			$title = get_the_title ($cookiePageId);
			echo "<a href=\"$url\">$title</a>";
		}

		echo '</p>';
		?>
	</div>

	<p class="footer-legal">&copy; <?=date ("Y")?> Alternative Experience&reg;. Todos los derechos reservados.</p>
	</div>
	<?php
	wp_footer ();
	?>
</body>
</html>