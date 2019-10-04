<?php

declare(strict_types=1);

namespace Rose\Utils;

final class Arrays
{
    static public function toArray(\Nette\Utils\ArrayHash $input): array
    {
        $output = array();
        foreach($input as $key => $value)
        {
            $output[$key] = $value;
        }
        return $output;
    }
}