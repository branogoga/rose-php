<?php

declare(strict_types=1);

namespace Rose\Utils\Strings;

final class Word
{
    public static function cleanUp(string $text, bool $ignoreFont = true, bool $removeStyles = true, bool $keepStructure = true): string 
    {
		$text = \Nette\Utils\Strings::replace($text, "/<o:p>\s*<\/o:p>/", '');
		$text = \Nette\Utils\Strings::replace($text, "/<o:p>[\s\S]*?<\/o:p>/", '&nbsp;');
		
		// Remove mso-xxx styles.
		$text = \Nette\Utils\Strings::replace($text, '/\s*mso-[^:]+:[^;\"]+;?/i', '') ;
	
		// Remove margin styles.
		$text = \Nette\Utils\Strings::replace($text, '/\s*MARGIN: 0cm 0cm 0pt\s*;/i', '') ;
		$text = \Nette\Utils\Strings::replace($text, '/\s*MARGIN: 0cm 0cm 0pt\s*"/i', '\"') ;
	
		$text = \Nette\Utils\Strings::replace($text, "/\s*TEXT-INDENT: 0cm\s*;/i", '') ;
		$text = \Nette\Utils\Strings::replace($text, '/\s*TEXT-INDENT: 0cm\s*"/i', '\"') ;
	
		$text = \Nette\Utils\Strings::replace($text, '/\s*TEXT-ALIGN: [^\s;]+;?"/i', '\"') ;
	
		$text = \Nette\Utils\Strings::replace($text, '/\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/i', '\"') ;
	
		$text = \Nette\Utils\Strings::replace($text, '/\s*FONT-VARIANT: [^\s;]+;?"/i', '\"') ;
	
		$text = \Nette\Utils\Strings::replace($text, '/\s*tab-stops:[^;"]*;?/i', '') ;
		$text = \Nette\Utils\Strings::replace($text, '/\s*tab-stops:[^"]*/i', '') ;
		
		// Remove FONT face attributes.
		if ( $ignoreFont )
		{
			$text = \Nette\Utils\Strings::replace($text, ' /\s*face="[^"]*"/i', '') ;
			$text = \Nette\Utils\Strings::replace($text, ' /\s*face=[^ >]*/i', '') ;
	
			$text = \Nette\Utils\Strings::replace($text, ' /\s*FONT-FAMILY:[^;"]*;?/i', '') ;
		}
		
		// Remove Class attributes
		$text = \Nette\Utils\Strings::replace($text, '/<(\w[^>]*) class=([^ |>]*)([^>]*)/i', "<$1$3") ;
	
		// Remove styles.
		if ( $removeStyles )
			$text = \Nette\Utils\Strings::replace($text, ' /<(\w[^>]*) style="([^\"]*)"([^>]*)/i', "<$1$3") ;
	
		// Remove style, meta and link tags
		$text = \Nette\Utils\Strings::replace($text, ' /<STYLE[^>]*>[\s\S]*?<\/STYLE[^>]*>/i', '') ;
		$text = \Nette\Utils\Strings::replace($text, ' /<(?:META|LINK)[^>]*>\s*/i', '') ;
		
		// Remove empty styles.
		$text = \Nette\Utils\Strings::replace($text, ' /\s*style="\s*"/i', '') ;
	
		$text = \Nette\Utils\Strings::replace($text, ' /<SPAN\s*[^>]*>\s*&nbsp;\s*<\/SPAN>/i', '&nbsp;') ;
	
		$text = \Nette\Utils\Strings::replace($text, ' /<SPAN\s*[^>]*><\/SPAN>/i', '') ;
	
		// Remove Lang attributes
		$text = \Nette\Utils\Strings::replace($text, '/<(\w[^>]*) lang=([^ |>]*)([^>]*)/i', "<$1$3") ;
	
		$text = \Nette\Utils\Strings::replace($text, ' /<SPAN\s*>([\s\S]*?)<\/SPAN>/i', '$1') ;
	
		$text = \Nette\Utils\Strings::replace($text, ' /<FONT\s*>([\s\S]*?)<\/FONT>/i', '$1') ;
	
		// Remove XML elements and declarations
		$text = \Nette\Utils\Strings::replace($text, '/<\\?\?xml[^>]*>/i', '') ;
	
		// Remove w: tags with contents.
		$text = \Nette\Utils\Strings::replace($text, ' /<w:[^>]*>[\s\S]*?<\/w:[^>]*>/i', '') ;
	
		// Remove Tags with XML namespace declarations: <o:p><\/o:p>
		$text = \Nette\Utils\Strings::replace($text, '/<\/?\w+:[^>]*>/i', '') ;
	
		// Remove comments [SF BUG-1481861].
		$text = \Nette\Utils\Strings::replace($text, '/<\!--[\s\S]*?-->/', '') ;
	
		$text = \Nette\Utils\Strings::replace($text, ' /<(U|I|STRIKE)>&nbsp;<\/\1>/', '&nbsp;') ;
	
		$text = \Nette\Utils\Strings::replace($text, ' /<H\d>\s*<\/H\d>/i', '') ;
	
		// Remove "display:none" tags.
		$text = \Nette\Utils\Strings::replace($text, ' /<(\w+)[^>]*\sstyle="[^"]*DISPLAY\s?:\s?none[\s\S]*?<\/\1>/i', '') ;
	
		// Remove language tags
		$text = \Nette\Utils\Strings::replace($text, ' /<(\w[^>]*) language=([^ |>]*)([^>]*)/i', "<$1$3") ;
	
		// Remove onmouseover and onmouseout events (from MS Word comments effect)
		$text = \Nette\Utils\Strings::replace($text, ' /<(\w[^>]*) onmouseover="([^\"]*)"([^>]*)/i', "<$1$3") ;
		$text = \Nette\Utils\Strings::replace($text, ' /<(\w[^>]*) onmouseout="([^\"]*)"([^>]*)/i', "<$1$3") ;
		
		if ( $keepStructure )
		{
			// The original <Hn> tag send from Word is something like this: <Hn style="margin-top:0px;margin-bottom:0px">
			$text = \Nette\Utils\Strings::replace($text, ' /<H(\d)([^>]*)>/i', '<h$1>') ;
	
			// Word likes to insert extra <font> tags, when using MSIE. (Wierd).
			$oldText = "";
			while( $oldText != $text )
			{
				$oldText = $text;
				$text = \Nette\Utils\Strings::replace($oldText, ' /<em><\/em>/i', '');
				$text = \Nette\Utils\Strings::replace($text, ' /<p><\/p>/i', '');
				$text = \Nette\Utils\Strings::replace($text, ' /<div><\/div>/i', '');
				$text = \Nette\Utils\Strings::replace($text, ' /<span><\/span>/i', '');
			}
			
			$text = \Nette\Utils\Strings::replace($text, ' /<(H\d)><FONT[^>]*>([\s\S]*?)<\/FONT><\/\1>/i', '<$1>$2<\/$1>');
			$text = \Nette\Utils\Strings::replace($text, ' /<(H\d)><EM>([\s\S]*?)<\/EM><\/\1>/i', '<$1>$2<\/$1>');
		}	
		else
		{
			$text = \Nette\Utils\Strings::replace($text, ' /<H1([^>]*)>/i', '<div$1><b><font size="6">') ;
			$text = \Nette\Utils\Strings::replace($text, ' /<H2([^>]*)>/i', '<div$1><b><font size="5">') ;
			$text = \Nette\Utils\Strings::replace($text, ' /<H3([^>]*)>/i', '<div$1><b><font size="4">') ;
			$text = \Nette\Utils\Strings::replace($text, ' /<H4([^>]*)>/i', '<div$1><b><font size="3">') ;
			$text = \Nette\Utils\Strings::replace($text, ' /<H5([^>]*)>/i', '<div$1><b><font size="2">') ;
			$text = \Nette\Utils\Strings::replace($text, ' /<H6([^>]*)>/i', '<div$1><b><font size="1">') ;
	
			$text = \Nette\Utils\Strings::replace($text, ' /<\/H\d>/i', '<\/font><\/b><\/div>') ;
	
			//// Transform <P> to <DIV>
			$text = \Nette\Utils\Strings::replace($text, '/(<P)([^>]*>[\\s\\S]*?)(<\/P>)/i','<div$2<\/div>');
	
			// Remove empty tags (three times, just to be sure).
			// This also removes any empty anchor
			$text = \Nette\Utils\Strings::replace($text, ' /<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/', '') ;
			$text = \Nette\Utils\Strings::replace($text, ' /<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/', '') ;
			$text = \Nette\Utils\Strings::replace($text, ' /<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/', '') ;
		}	
		
		return trim($text);	
    }
}

