<?php

Autoloader::add_core_namespace('Gallery');

Autoloader::add_classes(array(
	/**
	 * Gallery class.
	 */
	'Gallery\\Gallery'						=> __DIR__.'/classes/gallery.php',
));