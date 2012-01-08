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
 * Gallery html class
 *
 * @package  Gallery
 * @author   Phil_F
 */
class Gallery_Html
{
	// Declare the class vaiables
	protected static $gallery_table = null;
	protected static $image_table = null;

	public function __construct()
	{
		static::$gallery_table = \Config::get('gallery.gallery_table');
		static::$image_table = \Config::get('gallery.image_table');

	}

	/**
	* Build and return the intial display of galleries
	* and the sub galleries if called with $id
	* and the relivent breadcrumbs 
	*
	* @param id Optional - use if you wish to view a sub gallery
	*
	* @return array containing html to display gallery and breadcrumbs to navigate
	*/
	public static function gallery_html($id = 0)
    {
        // Need standard variables rather than 
        // static::$gallery_table
        // and 
        // static::$image_table 
        // for building initial $query
        $gallery_table = static::$gallery_table;
        $image_table = static::$image_table;

        // Used if a sub gallery is requested
        $view_sub_gallery = (int) $id;
        $has_subs = array();

        // Counter for the layout of the images across the page
        $counter = 0;

        // If a sub gallery was requested
        if ($view_sub_gallery)
        {
            $query = "SELECT $gallery_table.id, $gallery_table.name, $gallery_table.filename, $gallery_table.parent_id, COUNT($image_table.id) AS image_count
                        FROM $gallery_table
                        LEFT JOIN $image_table ON $image_table.gallery_id = $gallery_table.id
                        WHERE $gallery_table.parent_id = $view_sub_gallery
                        GROUP BY $gallery_table.id";
        }
        // If no sub gallery load all of them
        else
        {
            $query = "SELECT $gallery_table.id, $gallery_table.name, $gallery_table.filename, $gallery_table.parent_id, COUNT($image_table.id) AS image_count
                        FROM $gallery_table
                        LEFT JOIN $image_table ON $image_table.gallery_id = $gallery_table.id
                        GROUP BY $gallery_table.id";
        }

        // Run the query to get the galleries and how many images in each
        $result = \DB::query($query)->execute()->as_array();

        foreach ($result as $gallery)
        {
        	// If the gallery has a parent gallery the store it for later processing
            if ($gallery['parent_id'])
            {
                $has_subs[] = $gallery['parent_id'];
            }
        }

        $output = '<div class="gallery_row">'."\n";

        // Loop through all results
        // Build the html and store it in $output        
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
                if ($counter === \Config::get('gallery.gallery_thumb_per_row'))
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

                if (\Config::get('gallery.gallery_thumb_display_name'))
                {
                    $output .= "\t\t\t".'<div class="gallery_name">'.$gallery['name']."</div>\n";
                }

                $output .= "\t\t\t";

                if (file_exists(DOCROOT.\Config::get('gallery.gallery_thumb_path').$gallery['filename']) and ! empty($gallery['filename']))
                {
                    $img = \Config::get('gallery.gallery_thumb_path').$gallery['filename'];
                }
                else
                {
                    $img = \Config::get('gallery.gallery_thumb_path').\Config::get('gallery.gallery_thumb_image_not_found');
                }

                $output .= \Html::anchor($gallery_link, \Html::img($img, array(
                        'alt' => $gallery['name'],
                        'title' => $gallery['name'],
                        'width' => \Config::get('gallery.gallery_thumb_image_width').'px',
                        'height' => \Config::get('gallery.gallery_thumb_image_height').'px',
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

        // If there are sub galleries build the breadcrumb with them in
        if ($view_sub_gallery)
        {
            $breadcumb_result = \DB::select()->from(static::$gallery_table)->where('id', '=', $result[0]['parent_id'])->execute()->as_array();
            $breadcrumb = '<div class="breadcrumb">'.\Html::anchor(\Config::get('gallery.frontend_controller_gallery'), \Config::get('gallery.gallery_title'));
            $breadcrumb .= \Config::get('gallery.breadcrumb_separator');
            $breadcrumb .= $breadcumb_result[0]['name'];
            $breadcrumb .= '</div>'."\n";
        }
        // If there are no sub galleries just add the gallery title
        else
        {
            $breadcrumb = '<div class="breadcrumb">'.\Config::get('gallery.gallery_title').'</div>'."\n";
        }

        // Build the return array
        $ret = array('breadcrumb' => $breadcrumb, 'data' => $output);

        // Return the array containing the html and breadcrumbs
        return $ret;
    }


	/**
	* Build and return the image thumbs for a gallery
	* and the relivent breadcrumbs 
	*
	* @param id The gallery to view
	*
	* @return array containing html to display image thumbs and breadcrumbs to navigate
	*/
    public static function thumbs_html($id = null)
    {
        $output = null;
        $view_gallery = (int) $id;

        // Counter for the layout of the images across the page
        $counter = 0;

        // Query retunes the images contained within the specified gallery
        $result = \DB::select()->from(static::$image_table)->where('gallery_id', '=', $view_gallery)->execute()->as_array();

        // Loop through the results building the html
        if ( ! $result)
        {
            $img = \Config::get('gallery.gallery_thumb_path').\Config::get('gallery.gallery_thumb_image_not_found');
            $output .= '<div class="thumb_row">'."\n";
            $output .= "\t\t" . '<div class="thumb_link">'."\n";
            $output .= "\t\t\t";
            $output .= \Html::img($img, array(
                        'alt' => 'No image found',
                        'title' => 'No image found',
                        'width' => \Config::get('gallery.gallery_thumb_image_width') . 'px',
                        'height' => \Config::get('gallery.gallery_thumb_image_height') . 'px',
                ));
            $output .= "\t\t</div>\n";
            $output .= "</div>\n";
        }
        else
        {
            $output .= '<div class="thumb_row">'."\n";

            foreach ($result as $image)
            {
                if ($counter === \Config::get('gallery.image_thumb_per_row'))
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

                if (file_exists(DOCROOT.\Config::get('gallery.image_thumb_path').$image['filename']) and ! empty($image['filename']))
                {
                    if (\Config::get('gallery.jquery_addon_tag'))
                    {
                        $img = \Config::get('gallery.image_path').$image['filename'];
                        $link = \Config::get('gallery.image_path').$image['filename'];
                    }
                    else
                    {
                        $img = \Config::get('gallery.image_thumb_path').$image['filename'];
                        $link = \Config::get('gallery.frontend_controller_image').$image['id'];
                    }
                }
                else
                {
                    if (\Config::get('gallery.jquery_addon_tag'))
                    {
                        $img = \Config::get('gallery.image_path').\Config::get('gallery.full_image_not_found');
                        $link = \Config::get('gallery.image_path').\Config::get('gallery.full_image_not_found');
                    }
                    else
                    {
                        $img = \Config::get('gallery.image_thumb_path').\Config::get('gallery.image_thumb_image_not_found');
                        $link = '';
                    }
                }

                $output .= \Html::anchor($link, \Html::img($img, array(
                        'alt' => $image['caption'],
                        'title' => $image['caption'],                        
                        'width' => \Config::get('gallery.image_thumb_image_width') . 'px',
                        'height' => \Config::get('gallery.image_thumb_image_height') . 'px',
                        )),
                        array(\Config::get('gallery.jquery_addon_tag') => \Config::get('gallery.jquery_addon_attrib'),
                                'title' => $image['caption'],
                                'class' => \Config::get('gallery.jquery_addon_class'),
                                )
                        );
                $output .= "\n";
                $output .= "\t\t";

                if (\Config::get('gallery.image_thumb_display_filename'))
                {
                    $output .= '<div class="image_caption">'.$image['filename'] . "</div>\n";
                    $output .= "\t\t";
                }

                $output .= '</div>' . "\n";
            }

            $output .= "\t\t</div>\n";
        }

        // Build the breadcrums
        $breadcumb_result = \DB::select()->from(static::$gallery_table)->where('id', '=', $view_gallery)->execute()->as_array();
        $breadcrumb = '<div class="breadcrumb">'.\Html::anchor(\Config::get('gallery.frontend_controller_gallery'), \Config::get('gallery.gallery_title'));
        $breadcrumb .= \Config::get('gallery.breadcrumb_separator');

        if ($breadcumb_result[0]['parent_id'])
        {
            $breadcrumb .= \Html::anchor(\Config::get('gallery.frontend_controller_gallery').$breadcumb_result[0]['parent_id'], $breadcumb_result[0]['name']);
            $breadcrumb .= \Config::get('gallery.breadcrumb_separator');
        }

        $breadcrumb .= 'Images';
        $breadcrumb .= '</div>'."\n";

        $ret = array('breadcrumb' => $breadcrumb, 'data' => $output);

        return $ret;
    }


	/**
	* Build and return the necessary html to display the nominated image
	* and the relivent breadcrumbs 
	*
	* @param id The image to display
	*
	* @return array containing html to display the selected image and breadcrumbs to navigate
	*/
    public static function image_html($id = 0)
    {
        $image_id = (int) $id;

        $result = \DB::select()->from(static::$image_table)->where('id', '=', $image_id)->execute()->as_array();
        $gallery_sub_result = \DB::select()->from(static::$gallery_table)->where('id', '=', $result[0]['gallery_id'])->execute()->as_array();
        $gallery_sub_detail = \DB::select()->from(static::$gallery_table)->where('id', '=', $gallery_sub_result[0]['parent_id'])->execute()->as_array();

        if ( ! $result)
        {
            $output = "\t" . '<div class="no_image">No image found</div>'."\n";
        }
        else
        {
            foreach ($result as $image)
            {
                if (file_exists(DOCROOT.\Config::get('gallery.image_thumb_path').$image['filename']) and ! empty($image['filename']))
                {
                    $img = \Config::get('gallery.image_path').$image['filename'];
                }
                else
                {
                    $img = \Config::get('gallery.image_path').\Config::get('gallery.full_image_not_found');
                }

                $output = '<div class="image_frame">'.\Html::img($img, array(
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

}