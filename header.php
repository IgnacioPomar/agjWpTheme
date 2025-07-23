<?php
$logo_id = get_option ('agj_logo_id');
$logo_url = $logo_id ? wp_get_attachment_image_url ($logo_id, 'full') : '';
$logo_txt = get_bloginfo ('description');
;
// YAGNI: Coger un logo si no hay ninguno
?>

<!DOCTYPE html>
<html <?=language_attributes ();?>>
<head>
    <meta charset="<?=bloginfo ('charset');?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?=wp_head ();?>
</head>
<body <?=body_class ();?>>
	<?=wp_body_open ();?>
    <header>
        <div id="mnuBg"><div id="mnuContainer">
        	<label for="menu-toggle" class="mnuLbl">☰</label>
            <input type="checkbox" id="menu-toggle" class="menu-toggle" />
        	<div id="brand">
        		<a href="<?=home_url ();?>">
            		<img src="<?=esc_url ($logo_url);?>" alt="Logo" class="site-logo" style="max-height: 80px;">
        			<span class="logoTxt"><?=$logo_txt?></span>
        		</a>
        	</div>
            <nav>
			<?php
			$mnuOpcs = getMnuAnchored ();
			// YAGNI: Gestinar para que el menú "se parta" en dos columnas con el logo en medio si así está configurado

			echo '<ul class="headMnu">';
			foreach ($mnuOpcs as $opc)
			{
				echo '<li><a href="' . $opc [0] . '" onclick="document.getElementById(\'menu-toggle\').checked = false; return true;">' . $opc [1] . '</a></li>';
			}
			echo '</ul>';

			?>
            </nav>
           
        </div></div>
    </header>
    <main>
