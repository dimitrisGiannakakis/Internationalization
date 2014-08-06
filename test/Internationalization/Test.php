<?php
namespace Internationalization;
use Internationalization\Locale\i18n;
use Internationalization\Helper\Number;
/**
 *
 **/
class Test extends \PHPUnit_Framework_TestCase
{
    public function testParsing()
    {
        $number = (108056/100);

        i18n::setLocale('el_GR');

        echo Number::numberToCurrency($number).PHP_EOL;
    }


}

