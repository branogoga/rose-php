<?php

declare(strict_types=1);

namespace Rose\Utils\Strings;

final class Charset
{
	
	public static function removePunctuation(string $text): string
	{
		$text = \Nette\Utils\Strings::toAscii($text);
		$text = str_replace(
            array("'","`",'"',"^"), 
            array("","","",""),
            $text
        );
		
		return $text;
	}
	
    public static function  makePunctuationInsensitiveSearchRegularExpression( string $text ): string
    {
        $text=self::removePunctuation($text);
        
        $text = str_replace(array("\\"), array(""),$text);  
        
        $arr_conv = array();
        $arr_conv['a'] = '[aáäAÁÄ]';
        $arr_conv['c'] = '[cčCČ]';
        $arr_conv['d'] = '[dďDĎ]';
        $arr_conv['e'] = '[eéěëEÉĚË]';
        $arr_conv['i'] = '[iíIÍ]';
        $arr_conv['l'] = '[lĺľLĹĽ]';
        $arr_conv['n'] = '[nňNŇ]';
        $arr_conv['o'] = '[oóöôOÓÖÔ]';
        $arr_conv['r'] = '[rřŕRŘŔ]';
        $arr_conv['s'] = '[sšSŠ]';
        $arr_conv['t'] = '[tťTŤ]';
        $arr_conv['u'] = '[uúůüUÚŮÜ]';
        $arr_conv['y'] = '[yýYÝ]';
        $arr_conv['z'] = '[zžZŽ]';
        $arr_conv['?'] = '[aáäbcčdďeéěfghiíjklľĺmnňoóöôrŕřsštťuúüvxyýzžAÁÄBCČDĎEÉĚFGHIÍJKLMNŇOÓÔÖRŔŘSŠTŤUÚŮÜVXYÝZŽ]';

        $result = strtr(strtolower($text), $arr_conv);

        return $result;	
    }
    
    public static function removeSpaces(string $text): string
    {
        return strtr($text," ","_");
    }
}
