<?php
setlocale (LC_ALL, 'es_ES.utf8');

get_header ();

// $themeBaseUri = get_template_directory_uri ();

?>

<style>

</style>
  
  

<?php

// Obtener las subpáginas de la página actual
// $subpages = get_pages (array ('child_of' => 46, 'sort_column' => 'menu_order'));
$subpages = get_pages (array ('parent' => 0, 'sort_column' => 'menu_order'));

showSubpages ($subpages);

// get_sidebar();
get_footer ();
