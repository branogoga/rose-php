<?php

declare(strict_types=1);

final class CharsetTest extends PHPUnit\Framework\TestCase
{
	public function		testRemovePunctuation(): void
	{
		self::assertEquals( 
            \Rose\Utils\Strings\Charset::CHARSET_NO_PUNCTUATION,
            \Rose\Utils\Strings\Charset::removePunctuation( \Rose\Utils\Strings\Charset::CHARSET_ISO8859_2 )
        );

		self::assertEquals( 
            "pajstun",
            \Rose\Utils\Strings\Charset::removePunctuation( "pajštún" )
        );

		self::assertEquals( 
            "sturovo",
            \Rose\Utils\Strings\Charset::removePunctuation( "štúrovo" )
        );
	}
        
    public function         testPunctuationInsensitiveSearchRegularExpression(): void
    {
        self::assertEquals( 
            "[aáäAÁÄ]",
            \Rose\Utils\Strings\Charset::makePunctuationInsensitiveSearchRegularExpression( "a" )
        );

        self::assertEquals( 
            "[aáäAÁÄ]",
            \Rose\Utils\Strings\Charset::makePunctuationInsensitiveSearchRegularExpression( "A" )
        );
    }

    public function testRemoveSpaces(): void
    {
        self::assertEquals( 
            "",
            \Rose\Utils\Strings\Charset::removeSpaces( "" )
        );

        self::assertEquals( 
            "no-spaces",
            \Rose\Utils\Strings\Charset::removeSpaces( "no-spaces" )
        );

        self::assertEquals( 
            "_some_spaces_",
            \Rose\Utils\Strings\Charset::removeSpaces( " some spaces " )
        );

        self::assertEquals( 
            "__more__spaces__",
            \Rose\Utils\Strings\Charset::removeSpaces( "  more  spaces  " )
        );

    }
}

