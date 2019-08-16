<?php

declare(strict_types=1);

final class CharsetTest extends PHPUnit\Framework\TestCase
{
	public function		testRemovePunctuation()
	{
		$this->assertEquals( 
            \Rose\Utils\Strings\Charset::CHARSET_NO_PUNCTUATION,
            \Rose\Utils\Strings\Charset::removePunctuation( \Rose\Utils\Strings\Charset::CHARSET_ISO8859_2 )
        );

		$this->assertEquals( 
            "pajstun",
            \Rose\Utils\Strings\Charset::removePunctuation( "pajštún" )
        );

		$this->assertEquals( 
            "sturovo",
            \Rose\Utils\Strings\Charset::removePunctuation( "štúrovo" )
        );
	}
        
    public function         testPunctuationInsensitiveSearchRegularExpression()
    {
        $this->assertEquals( 
            "[aáäAÁÄ]",
            \Rose\Utils\Strings\Charset::makePunctuationInsensitiveSearchRegularExpression( "a" )
        );

        $this->assertEquals( 
            "[aáäAÁÄ]",
            \Rose\Utils\Strings\Charset::makePunctuationInsensitiveSearchRegularExpression( "Ä" )
        );
    }

    public function testRemoveSpaces()
    {
        $this->assertEquals( 
            "",
            \Rose\Utils\Strings\Charset::removeSpaces( "" )
        );

        $this->assertEquals( 
            "no-spaces",
            \Rose\Utils\Strings\Charset::removeSpaces( "no-spaces" )
        );

        $this->assertEquals( 
            "_some_spaces_",
            \Rose\Utils\Strings\Charset::removeSpaces( " some spaces " )
        );

        $this->assertEquals( 
            "__more__spaces__",
            \Rose\Utils\Strings\Charset::removeSpaces( "  more  spaces  " )
        );

    }
}

