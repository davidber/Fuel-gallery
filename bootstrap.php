<?php

Autoloader::add_core_namespace('Gallery');

Autoloader::add_classes(array(
	/**
	 * Gallery class.
	 */
	'Gallery\\Gallery'						=> __DIR__.'/classes/gallery.php',
	'Gallery\\Gallery_Html'					=> __DIR__.'/classes/gallery/html.php',
	'Gallery\\Gallery_Section'				=> __DIR__.'/classes/gallery/section.php',
	'Gallery\\Gallery_Image'				=> __DIR__.'/classes/gallery/image.php',
));