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

	public function before()
	{
		parent::before();
		Package::load('gallery');
	}

	public function action_index($gallery_id = null)
	{
		if ($gallery_id)
		{
			$gallery = Gallery::html()->gallery_html((int)$gallery_id);
		}
		else
		{
			$gallery = Gallery::html()->gallery_html();
		}
		
		$this->template->set_global('gallery', $gallery);

		$this->template->title = '';
		$this->template->content = View::forge('gallery/index');
	}

	public function action_thumb($thumb_id = null)
	{
		if ($thumb_id)
		{
			$gallery = Gallery::html()->thumbs_html((int)$thumb_id);
		}
		else
		{
			$gallery = Gallery::html()->thumbs_html();
		}
		
		$this->template->set_global('gallery', $gallery);

		$this->template->title = '';
		$this->template->content = View::forge('gallery/index');
	}

	public function action_image($image_id = null)
	{
		if ($image_id)
		{
			$gallery = Gallery::html()->image_html((int)$image_id);
		}
		
		$this->template->set_global('gallery', $gallery);

		$this->template->title = '';
		$this->template->content = View::forge('gallery/index');
	}
}
/* end of gallery.php */