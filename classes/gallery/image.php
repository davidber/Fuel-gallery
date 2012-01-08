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

class Gallery_Image
{

	protected static $gallery_table = null;
	protected static $image_table = null;

	public function __construct()
	{
		static::$gallery_table = \Config::get('gallery.gallery_table');
		static::$image_table = \Config::get('gallery.image_table');
	}

	public static function by_gallery_id($id = null)
    {
        $gallery_id = (int) $id;

        return \DB::select()->from(static::$image_table)->where('gallery_id', '=', $gallery_id)->execute()->as_array();
    }

    public static function by_id($id = null)
    {
        $image_id = (int) $id;

        return \DB::select()->from(static::$image_table)->where('id', '=', $image_id)->execute()->as_array();
    }

    public static function create($data = array())
    {
        $create_image = (array) $data;

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

        \DB::insert(static::$image_table)->set($create_image)->execute();

        static::_create_thumb(array('type' => 'image', 'filename' => $create_image['filename']));

        return;        
    }

    public static function update($data = array())
    {
        $update_image = (array) $data;

        if (array_key_exists('filename', $update_image))
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
                static::_create_thumb(array('type' => 'image', 'filename' => $update_image['filename']));
            }
            else
            {
                return array('No files to upload');
            }
        }

        \DB::update(static::$image_table)->set($update_image)->where('id', '=', $update_image['id'])->execute();

        return;        
    }

    public static function delete($id = null)
    {
        $delete_id = (int) $id;

        $images = \DB::select()->from(static::$image_table)->where('id', '=', $delete_id)->execute()->as_array();
        
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

        $deleted_images = \DB::delete(static::$image_table)->where('id', '=', $delete_id)->execute();

        return;
    }

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