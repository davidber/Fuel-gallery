<?php
/**
 * Sub class for the Gallery package for FuelPHP.
 *
 * @package    Gallery
 * @version    2.0
 * @author     Phil_F
 * @license    MIT License
 * @copyright  2012 Weztec Limited
 * @link       http://www.weztec.com
 */

namespace Gallery;

/**
 * Gallery Section class
 *
 * @package  Gallery
 * @author   Phil_F
 */
class Gallery_Section
{
    // Declare the class vaiables
	protected static $gallery_table = null;
	protected static $image_table = null;

	public function __construct()
	{
        // Initialise the class vaiables
		static::$gallery_table = \Config::get('gallery.gallery_table');
		static::$image_table = \Config::get('gallery.image_table');
	}

    /**
    * Get the section of the gallery by the parent id
    *
    * @param    id The sections with the parent id equal to $id (sub gallery id)
    *
    * @return   query The result set
    */
	public static function by_parent($id = null)
    {
        $parent_id = (int) $id;

        $sql = "SELECT * FROM ".static::$gallery_table.(isset($parent_id) ? ' WHERE parent_id = '.$parent_id : '');

        return \DB::Query($sql)->execute()->as_array();
    }

    /**
    * Get the specific section of the gallery by the id
    *
    * @param    id The specific section that matches the passed id (sub gallery id)
    *
    * @return   query The result set
    */
    public static function by_id($id = null)
    {
        $gallery_id = (int) $id;

        return \DB::select()->from(static::$gallery_table)->where('id', '=', $gallery_id)->execute()->as_array();
    }

    /**
    * Get all section of the gallery
    *
    * @return   query The result set
    */
    public static function all()
    {
        return \DB::select()->from(static::$gallery_table)->execute()->as_array();
    }

    /**
    * Create a new section of the gallery (sub gallery)
    *
    * @param    array Containing the creation data
    *
    * Example usage:
    * \code
    * array(
    *    'name' => 'My Gallery',    
    *    'filename' => 'test.jpg',
    *    'parent_id' => 5
    * )
    * \endcode
    *
    * @return   query The result set
    */
    public static function create($data = array())
    {
        $new_section = (array) $data;

        $config = array(
            'path' => DOCROOT.\Config::get('gallery.image_path'),
            'create_path' => true,
            'normalize' => true,
            'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
            'overwrite' => true,
        );

        \Upload::process($config);

        if (\Upload::is_valid())
        {
            \Upload::save();
        }
        else
        {
            return array('No files to upload');
        }

        \DB::insert(static::$gallery_table)->set($new_section)->execute();

        static::_create_thumb(array('type' => 'gallery', 'filename' => $new_section['filename']));

        /*
        foreach (Upload::get_errors() as $file)
        {
            // $file is an array with all file information,
            // $file['errors'] contains an array of all error occurred
            // each array element is an an array containing 'error' and 'message'
        }
        */

        //return \Upload::get_errors();

        return; // $success
    }

    /**
    * Update a section of the gallery (sub gallery)
    *
    * @param    array Containing the update data
    *
    * Example usage:
    * \code
    * array(
    *    'id' => 7,    
    *    'name' => 'My Gallery',    
    *    'filename' => 'test.jpg',
    *    'parent_id' => 5
    * )
    * \endcode
    *
    * @warning If you are not updating the filename exclude it from the array
    *
    * @return   query The result set
    */
    public static function update($data = array())
    {
        $update_gallery = (array) $data;

        //die(print_r($update_gallery));

        if (array_key_exists('filename', $update_gallery))
        {
            $config = array(
                'path' => DOCROOT.\Config::get('gallery.image_path'),
                'create_path' => true,
                'normalize' => true,
                'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
                'overwrite' => true,
            );

            \Upload::process($config);

            if (\Upload::is_valid())
            {
                \Upload::save();
                static::_create_thumb(array('type' => 'gallery', 'filename' => $update_gallery['filename']));
            }
            else
            {
                return array('No files to upload');
            }
        }

        \DB::update(static::$gallery_table)->set($update_gallery)->where('id', '=', $update_gallery['id'])->execute();

        /*
        foreach (Upload::get_errors() as $file)
        {
            // $file is an array with all file information,
            // $file['errors'] contains an array of all error occurred
            // each array element is an an array containing 'error' and 'message'
        }
        */

        //return \Upload::get_errors();

        return; // $success
    }

    /**
    * Delete a section of the gallery (sub gallery)
    *
    * @param    id The id of the section to delete (gallery)
    *
    * @warning This will delete all table entries and images associated with the section
    *
    * @return   success Return true or false
    */
    public static function delete($id = null)
    {
        $delete_id = (int) $id;

        $galleries = \DB::select()->from(static::$gallery_table)->where('id', '=', $delete_id)->execute()->as_array();
        $images = \DB::select()->from(static::$image_table)->where('gallery_id', '=', $delete_id)->execute()->as_array();

        foreach($galleries as $gallery)
        {
            // Delete the gallery images
            try
            {
                \File::delete(DOCROOT.\Config::get('gallery.gallery_thumb_path').$gallery['filename']);
            }
            catch(\InvalidPathException $e)
            {
                // The file does not exist
            }

        }

        foreach($images as $image)
        {
            // Delete the thumb and fullsize images
            try
            {
                \File::delete(DOCROOT.\Config::get('gallery.image_thumb_path').$image['filename']);
            }
            catch(\InvalidPathException $e)
            {
                // The file does not exist
            }

            try
            {
                \File::delete(DOCROOT.\Config::get('gallery.image_path').$image['filename']);
            }
            catch(\InvalidPathException $e)
            {
                // The file does not exist
            }
        }

        $deleted_galleries = \DB::delete(static::$gallery_table)->where('id', '=', $delete_id)->execute();
        $deleted_images = \DB::delete(static::$image_table)->where('gallery_id', '=', $delete_id)->execute();

        return; // $success
    }

    /**
    * Create a new thumb image
    *
    * @param    array The array that is passed to create or update
    *
    * @warning This can not be called directly 
    *
    * @return   success Return true or false
    */    
    private static function _create_thumb($data = array())
    {
        $image = (array) $data;
        $filename = \Config::get('gallery.image_path').$image['filename'];
        $image_type = $image['type'];

        if ($image_type === 'gallery')
        {
            $thumb_filename = \Config::get('gallery.gallery_thumb_path').$image['filename'];
            $width = \Config::get('gallery.gallery_thumb_image_width');
            $height = \Config::get('gallery.gallery_thumb_image_height');
        }

        if ($image_type === 'image')
        {
            $thumb_filename = \Config::get('gallery.image_thumb_path').$image['filename'];
            $width = \Config::get('gallery.image_thumb_image_width');
            $height = \Config::get('gallery.image_thumb_image_height');
        }

        \Image::load($filename)
            ->resize($width, $height, true, true)
            ->save($thumb_filename);

        return;
    }
     	
}