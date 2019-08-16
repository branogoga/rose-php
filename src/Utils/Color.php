<?php

declare(strict_types=1);

namespace Rose\Utils;

final class Color
{
    static public function calculateGradient(string $startHexRGB, string $endHxRGB, int $steps): array
    {
        $start['r'] = hexdec(substr($startHexRGB, 0, 2));
        $start['g'] = hexdec(substr($startHexRGB, 2, 2));
        $start['b'] = hexdec(substr($startHexRGB, 4, 2));

        $end['r'] = hexdec(substr($endHxRGB, 0, 2));
        $end['g'] = hexdec(substr($endHxRGB, 2, 2));
        $end['b'] = hexdec(substr($endHxRGB, 4, 2));

        $step['r'] = ($start['r'] - $end['r']) / ($steps - 1);
        $step['g'] = ($start['g'] - $end['g']) / ($steps - 1);
        $step['b'] = ($start['b'] - $end['b']) / ($steps - 1);

        $gradient = array();

        for($i = 0; $i < $steps; $i++)
        {

            $rgb['r'] = floor($start['r'] - ($step['r'] * $i));
            $rgb['g'] = floor($start['g'] - ($step['g'] * $i));
            $rgb['b'] = floor($start['b'] - ($step['b'] * $i));

            $hex['r'] = sprintf('%02x', ($rgb['r']));
            $hex['g'] = sprintf('%02x', ($rgb['g']));
            $hex['b'] = sprintf('%02x', ($rgb['b']));

            $gradient[] = implode("", $hex);

        }

        return $gradient; 
    }
}