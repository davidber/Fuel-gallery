# FuelPHP Gallery Package.

A gallery package for the FuelPHP framework.

Current version 2.0

# Summary

* No additional dependancies (Pure FuelPHP).
* Handels file uploads for you.
* Requires only a single frontend view file.
* Returns full html code for frontend.
* No Model required for frontend or administration area.
* Support for two levels of galleries.
* Support for basic breadcumb navigation.
* Configurable database name settings.
* Configurable controller name settings.
* Configurable gallery image settings.
* Configurable path settings.
* jQuery Lightbox2 support. 
* jQuery Fancybox support.
* jQuery Thickbox support.

# Database structure

	CREATE TABLE IF NOT EXISTS `galleries` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(50) NOT NULL,
	  `filename` varchar(50) NOT NULL,
	  `parent_id` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	)

	CREATE TABLE IF NOT EXISTS `gallery_images` (
  	`id` int(11) NOT NULL AUTO_INCREMENT,
  	`filename` varchar(50) NOT NULL,
  	`caption` text NOT NULL,
  	`gallery_id` int(11) NOT NULL,
  	PRIMARY KEY (`id`)
	)

# Usage

	/*
	* Gallery frontend html display functions
	*/

	$gallery = Gallery::html()->gallery_html();
	$sub_gallery = Gallery::html()->gallery_html($sub_gallery_id);
	$gallery_thumbs = Gallery::html()->thumbs_html($gallery_id);
	$gallery_image = Gallery::html()->image_html($image_id);


	/*
	* Gallery functions
	*/

	// Get all galleries
	$galleries = Gallery::section()->all();

	// Get all gallery parents
	$galleries = Gallery::section()->by_parent();

	// Get a specific specific gallery by parent id
	$gallery = Gallery::section()->by_parent(1);

	// Get a specific gallery by id
	$gallery = Gallery::section()->by_id(1);
	
	// Gallery create
	$new_gallery = array(
				'name' => (string) Input::post('name'),
				'filename' => (string) Input::post('filename'),
				'parent_id' => (string) Input::post('parent_id'),
				);

	$success = Gallery::section()->create($new_gallery);	

	// Gallery update
	$update_gallery = array(
				'id' => (int) $id,
				'name' => (string) Input::post('name'),
				'filename' => (string) Input::post('filename'),
				'parent_id' => (string) Input::post('parent_id'),
				);

	$success = Gallery::section()->update($update_gallery);

	//Gallery delete
	// *note this will delete gallery and all associated images
	$success = Gallery::section()->delete($id);


	/*
	* Image functions
	*/

	// Image create
	$new_image = array(
				'name' => (string) Input::post('name'),
				'filename' => (string) Input::post('filename'),
				'parent_id' => (string) Input::post('parent_id'),
				);

	$success = Gallery::image()->create($new_image);	

	// Gallery update
	$new_image = array(
				'id' => (int) $id,
				'name' => (string) Input::post('name'),
				'filename' => (string) Input::post('filename'),
				'parent_id' => (string) Input::post('parent_id'),
				);

	$success = Gallery::image()->update($update_image);

	// Get all images in a gallery
	$images = Gallery::image()->by_gallery_id($gallery_id);

	// Get a specific image
	$image = Gallery::image()->by_id($id);

	//Image delete	
	$success = Gallery::image()->delete($id);
	
# Example controller

    See the Examples folder

# Example single frontend view file 
   
    <?php
	echo Asset::css('gallery.css');
	echo '<div class="gallery">';
	echo html_entity_decode($gallery['breadcrumb'], ENT_QUOTES, 'UTF-8');
	echo html_entity_decode($gallery['data'], ENT_QUOTES, 'UTF-8');
	echo '</div>';
	?>