(function () {
	'use strict';

	var header = document.querySelector ('header');
	var toggle = document.getElementById ('menu-toggle');
	if (!header) return;

	var HIDDEN_CLASS = 'site-header--hidden';
	var TOP_OFFSET = 10; // px: considerado "arriba del todo"
	var HIDE_AFTER = 60; // px de scroll acumulado hacia abajo antes de ocultar

	var lastY = window.scrollY;
	var downAccum = 0;

	function show ()
	{
		header.classList.remove (HIDDEN_CLASS);
		downAccum = 0;
	}

	function onScroll ()
	{
		// Con el menú móvil abierto, la cabecera nunca se oculta
		if (toggle && toggle.checked)
		{
			show ();
			lastY = window.scrollY;
			return;
		}

		var y = window.scrollY;
		var diff = y - lastY;

		if (y <= TOP_OFFSET)
		{
			show ();
		}
		else if (diff < 0)
		{
			// Cualquier scroll hacia arriba, por pequeño que sea, muestra la cabecera
			show ();
		}
		else if (diff > 0)
		{
			downAccum += diff;
			if (downAccum > HIDE_AFTER)
			{
				header.classList.add (HIDDEN_CLASS);
			}
		}

		lastY = y;
	}

	window.addEventListener ('scroll', onScroll, { passive: true });

	// Al abrir el menú móvil, forzar que la cabecera esté visible
	if (toggle)
	{
		toggle.addEventListener ('change', function ()
		{
			if (toggle.checked)
			{
				show ();
			}
			lastY = window.scrollY;
		});
	}
}) ();
