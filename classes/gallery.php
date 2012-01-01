<?php
/**
 * @version    1.1
 * @author     Phil_F
 */

namespace Gallery;

class Gallery {

    public static function get_gallery_html($id = 0)
    {
        \Config::load('gallery', 'gallery');

        $gallery_table = \Config::get('gallery.gallery_table');
        $image_table = \Config::get('gallery.image_table');
        $view_sub_gallery = (int) $id;
        $has_subs = array();
        $counter = 0;

        if ($view_sub_gallery)
        {
            $query = "SELECT $gallery_table.id, $gallery_table.name, $gallery_table.filename, $gallery_table.parent_id, COUNT($image_table.id) AS image_count
                        FROM $gallery_table
                        LEFT JOIN $image_table ON $image_table.gallery_id = $gallery_table.id
                        WHERE $gallery_table.parent_id = $view_sub_gallery
                        GROUP BY $gallery_table.id";
        }
        else
        {        
            $query = "SELECT $gallery_table.id, $gallery_table.name, $gallery_table.filename, $gallery_table.parent_id, COUNT($image_table.id) AS image_count
                        FROM $gallery_table
                        LEFT JOIN $image_table ON $image_table.gallery_id = $gallery_table.id
                        GROUP BY $gallery_table.id";
        }

        $result = \DB::query($query)->execute()->as_array();

        foreach ($result as $gallery)
        {
            if ($gallery['parent_id'])
            {
                $has_subs[] = $gallery['parent_id'];
            }
        }

        $output = '<div class="gallery_row">'."\n";

        foreach ($result as $gallery)
        {
            if (in_array($gallery['id'], $has_subs))
            {
                $gallery_link = \Config::get('gallery.frontend_controller_gallery').$gallery['id'];
            }
            else
            {
                $gallery_link = \Config::get('gallery.frontend_controller_thumb').$gallery['id'];
            }

            if ($gallery['parent_id'] == $view_sub_gallery)
            {
                if ($counter === \Config::get('gallery.gallery_per_row'))
                {
                    $counter = 1;
                    $output .= "\t</div>\n";
                    $output .= "\n\t".'<div class="gallery_row">' . "\n";
                }
                else
                {
                    $counter++;
                }

                $output .= "\t\t".'<div class="gallery_link" >' . "\n";
                $output .= "\t\t\t".'<div class="gallery_name">'.$gallery['name']."</div>\n";
                $output .= "\t\t\t";

                if (file_exists(DOCROOT.\Config::get('gallery.thumb_path').$gallery['filename']) and ! empty($gallery['filename']))
                {
                    $img = \Config::get('gallery.thumb_path').$gallery['filename'];
                }
                else
                {
                    $img = \Config::get('gallery.thumb_path').\Config::get('gallery.gallery_image_not_found');
                }

                $output .= \Html::anchor($gallery_link, \Html::img($img, array(
                        'alt' => $gallery['name'],
                        'title' => $gallery['name'],
                        'width' => \Config::get('gallery.gallery_image_width').'px',
                        'height' => \Config::get('gallery.gallery_image_height').'px',
                )));
                $output .= "\n";
                $output .= "\t\t\t";
                if (in_array($gallery['id'], $has_subs))
                {
                    $output .= '<div class="gallery_count">Contains sub galleries'."</div>\n";
                }
                else
                {
                    $output .= '<div class="gallery_count">Images:'.$gallery['image_count']."</div>\n";
                }
                $output .= "\t\t";
                $output .= '</div>'."\n";
            }
        }

        $output .= "\t</div>\n";

        if ($view_sub_gallery)
        {
            $breadcumb_result = \DB::select()->from($gallery_table)->where('id', '=', $result[0]['parent_id'])->execute()->as_array();
            $breadcrumb = '<div class="breadcrumb">'.\Html::anchor(\Config::get('gallery.frontend_controller_gallery'), \Config::get('gallery.gallery_title'));
            $breadcrumb .= \Config::get('gallery.breadcrumb_separator');
            $breadcrumb .= $breadcumb_result[0]['name'];
            $breadcrumb .= '</div>'."\n";
        }
        else
        {
            $breadcrumb = '<div class="breadcrumb">'.\Config::get('gallery.gallery_title').'</div>'."\n";
        }

        $ret = array('breadcrumb' => $breadcrumb, 'data' => $output);

        return $ret;
    }

    public static function get_thumbs_html($id = null)
    {
        \Config::load('gallery', 'gallery');

        $gallery_table = \Config::get('gallery.gallery_table');
        $image_table = \Config::get('gallery.image_table');
        $output = null;
        $view_gallery = (int) $id;
        $counter = 0;

        $result = \DB::select()->from($image_table)->where('gallery_id', '=', $view_gallery)->execute()->as_array();

        if ( ! $result)
        {
            $img = \Config::get('gallery.thumb_path').\Config::get('gallery.thumb_image_not_found');
            $output .= '<div class="thumb_row">'."\n";
            $output .= "\t\t" . '<div class="thumb_link">'."\n";
            $output .= "\t\t\t";
            $output .= \Html::img($img, array(
                        'alt' => 'No image found',
                        'title' => 'No image found',
                        'width' => \Config::get('gallery.thumb_image_width') . 'px',
                        'height' => \Config::get('gallery.thumb_image_height') . 'px',
                ));
            $output .= "\t\t</div>\n";
            $output .= "</div>\n";
        }
        else
        {
            $output .= '<div class="thumb_row">'."\n";

            foreach ($result as $image)
            {
                if ($counter === \Config::get('gallery.thumbs_per_row'))
                {
                    $counter = 1;
                    $output .= "\t</div>\n";
                    $output .= "\n\t" . '<div class="thumb_row">'."\n";
                }
                else
                {
                    $counter++;
                }

                $output .= "\t\t" . '<div class="thumb_link" >'."\n";
                $output .= "\t\t\t";

                if (file_exists(DOCROOT.\Config::get('gallery.thumb_path').$image['filename']) and ! empty($image['filename']))
                {
                    $img = \Config::get('gallery.thumb_path').$image['filename'];
                }
                else
                {
                    $img = \Config::get('gallery.thumb_path').\Config::get('gallery.thumb_image_not_found');
                }

                $output .= \Html::anchor(\Config::get('gallery.frontend_controller_image').$image['id'], \Html::img($img, array(
                        'alt' => $image['caption'],
                        'title' => $image['caption'],
                        'width' => \Config::get('gallery.thumb_image_width') . 'px',
                        'height' => \Config::get('gallery.thumb_image_height') . 'px',
                )));
                $output .= "\n";
                $output .= "\t\t";

                if (\Config::get('gallery.thumb_display_filename'))
                {
                    $output .= '<div class="image_caption">'.$image['filename'] . "</div>\n";
                    $output .= "\t\t";
                }

                $output .= '</div>' . "\n";
            }

            $output .= "\t\t</div>\n";
        }
               
        $breadcumb_result = \DB::select()->from($gallery_table)->where('id', '=', $view_gallery)->execute()->as_array();

        $breadcrumb = '<div class="breadcrumb">'.\Html::anchor(\Config::get('gallery.frontend_controller_gallery'), \Config::get('gallery.gallery_title'));
        $breadcrumb .= \Config::get('gallery.breadcrumb_separator');

        if ($breadcumb_result[0]['parent_id'])
        {
            $gallery_sub_detail = \DB::select()->from($gallery_table)->where('id', '=', $breadcumb_result[0]['parent_id'])->execute()->as_array();
            $breadcrumb .= \Html::anchor(\Config::get('gallery.frontend_controller_gallery').$breadcumb_result[0]['parent_id'], $breadcumb_result[0]['name']);
            $breadcrumb .= \Config::get('gallery.breadcrumb_separator');
        }

        $breadcrumb .= 'Images';
        $breadcrumb .= '</div>'."\n";

        $ret = array('breadcrumb' => $breadcrumb, 'data' => $output);

        return $ret;
    }

    public static function get_image_html($id = 0)
    {
        \Config::load('gallery', 'gallery');

        $gallery_table = \Config::get('gallery.gallery_table');
        $image_table = \Config::get('gallery.image_table');
        $image_id = (int) $id;

        $result = \DB::select()->from($image_table)->where('id', '=', $image_id)->execute()->as_array();
        $gallery_sub_result = \DB::select()->from($gallery_table)->where('id', '=', $result[0]['gallery_id'])->execute()->as_array();
        $gallery_sub_detail = \DB::select()->from($gallery_table)->where('id', '=', $gallery_sub_result[0]['parent_id'])->execute()->as_array();

        if ( ! $result)
        {
            $output = "\t" . '<div class="no_image">No image found</div>'."\n";
        }
        else
        {
            foreach ($result as $image)
            {
                if (file_exists(DOCROOT.\Config::get('gallery.thumb_path').$image['filename']) and ! empty($image['filename']))
                {
                    $img = \Config::get('gallery.image_path').$image['filename'];
                }
                else
                {
                    $img = \Config::get('gallery.image_path').\Config::get('gallery.full_image_not_found');
                }

                $output = '<div class="image_frame">'.\Html::img(\Config::get('gallery.image_path').$image['filename'], array(
                        'alt' => $image['caption'],
                        'title' => $image['caption'],
                        'class' => 'image',
                ))."</div>\n";

                $output .= '<div class="image_caption">'.$image['caption'].'</div>'."\n";
                $gallery_id = $image['gallery_id'];
            }
        }

        $breadcrumb = '<div class="breadcrumb">'.\Html::anchor(\Config::get('gallery.frontend_controller_gallery'), \Config::get('gallery.gallery_title'));
        $breadcrumb .= \Config::get('gallery.breadcrumb_separator');
        $breadcrumb .= \Html::anchor(\Config::get('gallery.frontend_controller_thumb').$gallery_id, $gallery_sub_detail[0]['name']);
        $breadcrumb .= \Config::get('gallery.breadcrumb_separator');
        $breadcrumb .= $image['filename'];
        $breadcrumb .= "</div>\n";

        $ret = array('breadcrumb' => $breadcrumb, 'data' => $output);

        return $ret;
    }

    public static function get_by_parent($id = null)
    {
        \Config::load('gallery', 'gallery');

        $gallery_table = \Config::get('gallery.gallery_table');
        $parent_id = (int) $id;

        $sql = "SELECT * FROM $gallery_table";
        if ( isset($parent_id))
        {
            $sql .= ' WHERE parent_id = '.$parent_id;
        }

        return \DB::Query($sql)->execute()->as_array();
    }

    public static function get_by_id($id = null)
    {
        \Config::load('gallery', 'gallery');

        $gallery_table = \Config::get('gallery.gallery_table');

        return \DB::select()->from($gallery_table)->where('id', '=', $id)->execute()->as_array();   
    }

    public static function create_gallery($data = array())    
    {
        \Config::load('gallery', 'gallery');

        $gallery_table = \Config::get('gallery.gallery_table');
        $new_gallery = (array) $data;

        $config = array(
            'path' => DOCROOT.\Config::get('gallery.image_path'),
            'create_path' => true,
            'normalize' => true,
            'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
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

        \DB::insert($gallery_table)->set($new_gallery)->execute();

        static::_create_thumb(array('type' => 'gallery', 'filename' => $new_gallery['filename']));

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

    public static function update_gallery($data = array())
    {
        \Config::load('gallery', 'gallery');

        $gallery_table = \Config::get('gallery.gallery_table');
        $update_gallery = (array) $data;

        if (in_array('filename', $update_gallery))
        {
            $config = array(
                'path' => DOCROOT.\Config::get('gallery.image_path'),
                'create_path' => true,
                'normalize' => true,
                'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
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

        \DB::update($gallery_table)->set($update_gallery)->where('id', '=', $update_gallery['id'])->execute();

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

    public static function delete_gallery($id = null)
    {
        \Config::load('gallery', 'gallery');

        $gallery_table = \Config::get('gallery.gallery_table');
        $image_table = \Config::get('gallery.image_table');
        $delete_id = (int) $id;

        $galleries = \DB::select()->from($gallery_table)->where('id', '=', $delete_id)->execute()->as_array();
        $images = \DB::select()->from($image_table)->where('gallery_id', '=', $delete_id)->execute()->as_array();
        
        foreach($galleries as $gallery)
        {
            // Delete the gallery images
            if (file_exists(DOCROOT.\Config::get('gallery.thumb_path').$gallery['filename']))
            {
                \File::delete(DOCROOT.\Config::get('gallery.thumb_path').$gallery['filename']);                 
            }
        }

        foreach($images as $image)
        {
            // Delete the thumb and fullsize images
            if (file_exists(DOCROOT.\Config::get('gallery.thumb_path').$image['filename']))
            {
                \File::delete(DOCROOT.\Config::get('gallery.thumb_path').$image['filename']);                 
            }

            if (file_exists(DOCROOT.\Config::get('gallery.image_path').$image['filename']))
            {
                \File::delete(DOCROOT.\Config::get('gallery.image_path').$image['filename']);                 
            }
        }

        $deleted_galleries = \DB::delete($gallery_table)->where('id', '=', $delete_id)->execute();
        $deleted_images = \DB::delete($image_table)->where('gallery_id', '=', $delete_id)->execute();

        return; // $success        
    }

    public static function create_image($data = array())
    {
        \Config::load('gallery', 'gallery');

        $image_table = \Config::get('gallery.image_table');
        $create_image = (array) $data;

        $config = array(
            'path' => DOCROOT.\Config::get('gallery.image_path'),
            'create_path' => true,
            'normalize' => true,
            'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
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

        \DB::insert($image_table)->set($create_image)->execute();

        static::_create_thumb(array('type' => 'gallery', 'filename' => $create_image['filename']));

        return;        
    }

    public static function update_image($data = array())
    {
        \Config::load('gallery', 'gallery');

        $image_table = \Config::get('gallery.image_table');
        $update_image = (array) $data;

        if (in_array('filename', $update_image))
        {
            $config = array(
                'path' => DOCROOT.\Config::get('gallery.image_path'),
                'create_path' => true,
                'normalize' => true,
                'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
            );

            \Upload::process($config);

            if (\Upload::is_valid())
            {
                \Upload::save();
                static::_create_thumb(array('type' => 'gallery', 'filename' => $update_image['filename']));
            }
            else
            {
                return array('No files to upload');
            }
        }

        \DB::update($image_table)->set($update_image)->where('id', '=', $update_image['id'])->execute();

        return;        
    }

    public static function delete_image($id = null)
    {
        \Config::load('gallery', 'gallery');

        $image_table = \Config::get('gallery.image_table');
        $delete_id = (int) $id;

        $images = \DB::select()->from($image_table)->where('id', '=', $delete_id)->execute()->as_array();
        
        foreach($images as $image)
        {
            // Delete the thumb and fullsize images
            if (file_exists(DOCROOT.\Config::get('gallery.thumb_path').$image['filename']))
            {
                \File::delete(DOCROOT.\Config::get('gallery.thumb_path').$image['filename']);                 
            }

            if (file_exists(DOCROOT.\Config::get('gallery.image_path').$image['filename']))
            {
                \File::delete(DOCROOT.\Config::get('gallery.image_path').$image['filename']);                 
            }
        }

        $deleted_images = \DB::delete($image_table)->where('id', '=', $delete_id)->execute();

        return;        
    }

    private static function _create_thumb($data = array())
    {
        \Config::load('gallery', 'gallery');
        
        $image = (array) $data;
        $filename = \Config::get('gallery.image_path').$image['filename'];
        $thumb_filename = \Config::get('gallery.thumb_path').$image['filename'];
        $image_type = $image['type'];

        if ($image_type === 'gallery')
        {
            $width = \Config::get('gallery.gallery_image_width');
            $height = \Config::get('gallery.gallery_image_height');
        }

        if ($image_type === 'image')
        {
            $width = \Config::get('gallery.thumb_image_width');
            $height = \Config::get('gallery.thumb_image_height');            
        }

        \Image::load($filename)
            ->resize($width, $height, true, true)
            ->save($thumb_filename);

        return;        
    }


}
