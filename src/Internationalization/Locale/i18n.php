<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Internationalization\Locale;
use Symfony\Component\Yaml\Yaml;

class i18n
{
    const EXT = ".yml";

    protected $translations;
    protected $locale = 'en_US';

    protected static $merged;

    public static $locales_path;

    private static $instance;

    private function __construct($locale="en_US", $category=LC_ALL, $string=null)
    {
        if (null === self::$locales_path) {
            self::$locales_path =
                __DIR__
                .'/'
                .'..'
                . DIRECTORY_SEPARATOR
                . "i18n"
                . DIRECTORY_SEPARATOR;
        }

        $this->load_locale_file($locale);
    }

    private function load_locale_file($locale)
    {
        if ( !$this->valid_locale($locale) ){
            $locale = "en_US";
        }

        $this->locale = $locale;

        $locale_file = self::$locales_path . $this->locale_filename();

        $this->translations = Yaml::parse(file_get_contents($locale_file));

        if ( null != self::$merged ){
            $this->translations = array_replace_recursive(
                $this->translations,
                self::$merged
            );
        }
    }

    private function locale_filename()
    {
        return $this->locale . self::EXT;
    }

    private function valid_locale($locale)
    {
        return true;
    }

    protected static function get_instance()
    {
        return isset(self::$instance) ? self::$instance : self::setLocale();
    }

    public static function merge($filepath)
    {
        self::$merged = yaml_parse_file($filepath);
    }

    public static function getLocale()
    {
        return self::get_instance()->locale;
    }

    public static function setLocale(
        $locale="en_US",
        $category = LC_ALL,
        $string = null
    ) {
        self::$instance = new static($locale, $category, $string);

        return self::$instance;
    }

    public static function translate($string, $options=array())
    {
        self::$instance = self::get_instance();
        return self::$instance->getTranslation($string, $options);
    }

    public static function t($string, $options = array())
    {
        return self::translate($string, $options);
    }

    private function scope_from_options($options)
    {
        if (!is_array($options) || !isset($options['scope'])) {

            return null;
        }

        if ( is_array($options['scope']) ){

            return implode(".", $options['scope']);
        } elseif( is_string($options['scope']) ){

            return $options['scope'];
        }
    }

    private function locale_from_options($options)
    {
        if ( !is_array($options) || !isset($options['locale']) )
            return $this->locale;

        $this->locale = $options['locale'];
        $this->load_locale_file($options['locale']);
        return $options['locale'];
    }

    public function getTranslation($string, $options = array())
    {
        $string = $this->locale_from_options($options) . "."
            . $this->scope_from_options($options) . "."
            . $string;

        if ( isset($options['locale']) ) unset($options['locale']);
        if ( isset($options['scope']) ) unset($options['scope']);

        $l = explode('.',$string);
        $l = array_filter($l, 'strlen');
        $last_index = count($l);
        $tmp_locale = $this->translations;
        $return_locale = "";

        foreach ($l as $index => $v) {

            if (!array_key_exists($v, $tmp_locale)) {
                break;
            }

            if ( isset($tmp_locale[$v]) && $index == $last_index ) {

                $return_locale = $tmp_locale[$v];

                if (!empty($options) ) {
                    foreach( $options as $k=>$o ) {
                        if (is_array($o)) {
                            continue;
                        }
                        $return_locale = str_replace('%{'.$k.'}', $o, $return_locale);
                    }
                }

                return $return_locale;
            } else {
                $tmp_locale = $tmp_locale[$v];
            }
        }

        return "Translation ".implode('->',$l). " not found!";
    }

    public function getLocalization($string, $options = array())
    {
        if ( !isset($options['format']) ) $options['format'] = 'default';

        $format = $options['format'];
        unset($options['format']);

        $time = is_numeric($string) ? $string : strtotime($string);

        $r = strftime("%H:%M", $time);

        $type = "00:00" == $r ? 'date' : 'time';

        $pattern = $this->getTranslation("{$type}.formats.{$format}", $options);

        preg_match_all("/%[aAbBp]/", $pattern, $matches);
        foreach ($matches[0] as $match) {
            switch ($match) {
            case "%a":
                $a = self::translate("date.abbr_day_names");
                $r = $a[strftime("%w",$time)];
                $f = "%a";
                break;
            case "%A":
                $a = self::translate("date.day_names");
                $r = $a[strftime("%w",$time)];
                $f = "%A";
                break;
            case "%b":
                $a = self::translate("date.abbr_month_names");
                $r = $a[(int)strftime("%m",$time)];
                $f = "%b";
                break;
            case "%B":
                $a = self::translate("date.month_names");
                $r = $a[(int) strftime("%m",$time)];
                $f = "%B";
                break;
            case "%p":
                $h = (int) strftime("%H", $time);
                $p = $h < 12 ? 'am' : 'pm';
                $r = self::translate("time.{$p}");
                $f = "%p";
                break;
            }
            $pattern = str_replace($f, $r, $pattern);
        }

        return strftime($pattern, $time);
    }

    public static function localize($string, $options = array())
    {
        return self::get_instance()->getLocalization($string, $options);
    }

    public static function l($string, $options = array())
    {
        return self::localize($string, $options);
    }

}
