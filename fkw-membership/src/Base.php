<?php
namespace FKW\Membership;

/**
 * Class Utility
 *
 * @author Christina Gleason <christina@finarina.com>
 * @since 1.0.0
 * @package finarinablock/src
 */
class Base {

    /**
     * The single instance of the class.
     *
     * @var Class|null
     */
    private static $instances = [];

	/**
     * Get the single instance of the class.
     *
     * @return Class|null
     */
    public static function get_instance() {
        $class = get_called_class();

        if ( ! isset( self::$instances[ $class ] ) ) {
            self::$instances[ $class ] = new static();
        }

        return self::$instances[ $class ];
    }

    /**
     * Constructor
     *
     * @author Christina Gleason <christina@finarina.com>
     * @since 1.0.0
     * @param none
     *
     * @return $this
     */
    public function __construct() {

    }

    /**
     * Validates and sanitizes what should be string values
     *
     * @author Christina Gleason
     * @param string $string - string to clean
     * @param boolean $commaDelimited - should this string replace spaces and returns with commas, defaults to false
     * @param string $returns - what to do with returns, either convert to spaces, strip Windows only, or strip all of them (spaces, strip, windows), defaults to spaces
     * @param boolean $quotes - should it remove all quotes (single and double), defaults to false
     * @return $string - the cleaned up string, will return null if invalid for security
     *
     */
    public function clean_string( $string, $commaDelimited = false, $returns = 'spaces', $quotes = false ) {
        // default on how it handles returns, which is converting them to spaces
        $returnsOptions = array("\t", "\r\n", "\n", "\r");
        $returns = " ";

        // if this needs to be comma delimited, make sure those are all in before removing bad characters or returns
        if($commaDelimited == true) {
            $string = str_replace($returnsOptions, ", ", $string);
        }

        // if its set to strip, remove entirely
        if($returns == 'strip') {
            $returns = "";
        // if its set to windows, remove windows based returns
        } else if($returns == 'windows') {
            $returnsOptions = "\r";
            $returns = "";
        }

        $string = str_replace($returnsOptions, $returns, $string);

        if($quotes == false) {
            $string = str_replace(array("\"",'"'), "", $string);
        }

        return $string;
    }

    /**
     * Validates and sanitizes what should be int
     *
     * @author Christina Gleason
     * @param int $int - integer to verify
     * @param int $max - the max number this can be, defaults to null
     * @param int $min - the min number this can be, defaults to null
     * @return $string - the cleaned up int, will return null if invalid for security
     *
     */
    public function clean_int( $int, $max = NULL, $min = NULL ) {
        $valid = true;

        if(is_int($int)) {
            if($max && $int > $max) {
                $valid = false;
            }

            if($min && $int < $min) {
                $valid = false;
            }
        } else {
            $valid = false;
        }

        if($valid == false) {
            return NULL;
        }

        return $int;
    }

}
