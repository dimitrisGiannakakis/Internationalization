<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Internationalization\Helper;

use Internationalization\Locale\i18n;
use Symfony\Component\Yaml\Parser;
/**
 * 
 **/
class Date 
{
   public static $DEFAULT_CURRENCY_VALUES = array(
        'format'          => "%d-%m-%Y"
    );

   public static function fullDate ($value, $options = array())
   {
       if ( null === $value ) return null;

        $params = array(
            'default' => array()
        );

        if ( isset($options['locale']) )
            $params = array_merge( $params, array('locale' => $options['locale']) );

        $defaults = i18n::translate("date.formats", $params);
        
        $defaults = array_merge(self::$DEFAULT_CURRENCY_VALUES, $defaults)
         if ( isset($options['format']) )
            $defaults['negative_format'] = "-" + $options['format'];

        $options = array_merge($defaults, $options);
        $format = $options['format'];
        unset($options['format']);
   }
}
