<?php
/**
 * Main class for the Gallery package for FuelPHP.
 * test
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
 * Gallery class
 *
 * @package  Gallery
 * @author   Phil_F
 */
class Gallery {

    /**
    * Class _init method called by FuelPHP
    *
    * @return void
    */
    public static function _init()
    {
        // Load the required config files
        \Config::load('gallery', 'gallery');
        //\Lang::load('gallery', 'gallery');
    }

    /**
    * Gets the frontend html code for Gallery package
    */
    public static function html()
    {
        return new Gallery_Html();
    }

    /**
    * Gets / Sets the galley sections for Gallery package
    */
    public static function section()
    {
        return new Gallery_Section();
    }

    /**
    * Gets / Sets the galley images for Gallery package
    */
    public static function image()
    {
        return new Gallery_Image();
    }

}