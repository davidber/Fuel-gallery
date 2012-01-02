<?php

/**
 * The Gallery Controller.
 *
 *
 *
 * @package
 * @extends  index
 */

class Controller_Gallery extends \Controller_Template {

	//public  = 'template-name';

	public function before()
	{
		parent::before();
		Package::load('gallery');
	}

	public function action_index($gallery_id = null)
	{
		if ($gallery_id)
		{
			$gallery = Gallery::get_gallery_html((int)$gallery_id);
		}
		else
		{
			$gallery = Gallery::get_gallery_html();
		}

		$this->template->set_global('gallery', $gallery);

		$this->template->title = '';
		$this->template->content = View::forge('gallery/index');
	}

	public function action_thumb($thumb_id = null)
	{
		if ($thumb_id)
		{
			$gallery = Gallery::get_thumbs_html((int)$thumb_id);
		}
		else
		{
			$gallery = Gallery::get_thumbs_html();
		}

		$this->template->set_global('gallery', $gallery);

		$this->template->title = '';
		$this->template->content = View::forge('gallery/index');
	}

	public function action_image($image_id = null)
	{
		if ($image_id)
		{
			$gallery = Gallery::get_image_html((int)$image_id);
		}

		$this->template->set_global('gallery', $gallery);

		$this->template->title = '';
		$this->template->content = View::forge('gallery/index');
	}
}
/* end of gallery.php */