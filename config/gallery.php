<?php
return array(
    /* Set up the database names */
    'gallery_table' => 'galleries',
    'image_table' => 'gallery_images',


	'frontend_controller_gallery' => 'gallery/index/',
    'frontend_controller_thumb' => 'gallery/thumb/',
    'frontend_controller_image' => 'gallery/image/',

    'gallery_title' => 'General Gallery',

    'breadcrumb_separator' => ' &gt; ',

    /* Set up the image class config to suit your needs
     *  The image class is used for thumb creation
     *  But file height and width are overridden by values below
    */
    

    /* Gallery and sub-gallery layout */
    'galleries_per_row' => 4,
    'galleries_image_width' => 175,
    'galleries_image_height' => 175,
    'galleries_image_not_found' => 'repair.gif',

    /* Thumb image layout */
    'thumb_path' => 'assets/userfiles/gallery/thumb/',
    'thumbs_per_row' => 4,
    'thumb_image_width' => 100,
    'thumb_image_height' => 100,
    'thumb_image_not_found' => 'repair.gif',
    'thumb_display_filename' => true,

    /* Full size image */
    'image_path' => 'assets/userfiles/gallery/img/',
    'full_image_not_found' => 'repair.gif',
);
