# FuelPHP Gallery Package.

A complete FuelPHP gallery package.

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

# Usage

	// Gallery display functions
	$gallery = Gallery::get_gallery_html();
	$sub_gallery = Gallery::get_gallery_html($sub_gallery_id);
	$gallery_thumbs = Gallery::get_thumbs_html($gallery_id);
	$gallery_image = Gallery::get_image_html($image_id);

	// General functions

	// Get all gallery parents
	$all_galleries = Gallery::get_by_parent();

	// Get a specific specific gallery by parent id
	$all_galleries = Gallery::get_by_parent(1);

	// Get a specific gallery by id
	$gallery_parent = Gallery::get_by_id(1);
	
	// Gallery create
	$new_gallery = array(
				'name' => (string) Input::post('name'),
				'filename' => (string) Input::post('filename'),
				'parent_id' => (string) Input::post('parent_id'),
				);

	$success = Gallery::create($new_gallery);	

	// Gallery update
	$new_gallery = array(
				'id' => (int) $id,
				'name' => (string) Input::post('name'),
				'filename' => (string) Input::post('filename'),
				'parent_id' => (string) Input::post('parent_id'),
				);

	$success = Gallery::update($update_gallery);

	//Gallery delete
	// *note this will delete gallery and all associated images
	$success = Gallery::delete($id);
	
# Example controller

    // Load the package
    Package::load('gallery');

# Example single frontend view file 
   
    <?php
	echo Asset::css('gallery.css');
	echo '<div class="gallery">';
	echo htmlspecialchars_decode($gallery['breadcrumb']);
	echo htmlspecialchars_decode($gallery['data']);
	echo '</div>';
	?>