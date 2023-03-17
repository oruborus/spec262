<?php

declare(strict_types=1);

namespace Oru\Spec262\Formatters;

use Oru\Spec262\Contracts\Formatter;

final class CurrentFormatter implements Formatter
{
    /**
     * @return string[]
     */
    public function format(string $content): array
    {
        $content = preg_replace('/(?<c>[_\*~`|])(?<word>\S+)\g{c}/m', '$2', $content);
        $lines = preg_split('/\R/u', $content, -1, PREG_SPLIT_NO_EMPTY);
        if ($lines === []) {
            return [];
        }

        $lines = array_filter($lines, static fn (string $line): bool => ltrim($line) !== '');

        $lines = $this->indentToArray($lines, strlen($lines[0]) - strlen(ltrim($lines[0])));

        return $lines;
    }

    /**
     * @param string[] $lines
     * @return string[]
     */
    private function indentToArray(array $lines, int $lastIndent, int $depth = 0): array
    {
        $result = [];
        $level = 1;

        while ($line = array_shift($lines)) {
            $indent = strlen($line) - strlen(ltrim($line));

            if ($indent < $lastIndent) {
                break;
            }

            if ($indent > $lastIndent) {
                $lines = [$line, ...$lines];
                $nested = $this->indentToArray($lines, $indent, $depth + 1);
                $lines = array_slice($lines, count($nested));
                $result = [...$result, ...$nested];
                continue;
            }

            $result[] = $this->convertMarker($level++, $depth) . '. ' . substr(trim($line), 3);
        }

        return $result;
    }

    private function convertMarker(int $level, int $depth): string
    {
        return match ($depth % 3) {
            0 => (string) $level,
            1 => $this->numToB26($level),
            2 => $this->numToRoman($level)
        };
    }

    private function numToB26(int $num): string
    {
        $b26 = '';

        do {
            $val = ($num % 26) ?: 26;
            $num = ($num - $val) / 26;
            $b26 = chr($val + 96) . $b26;
        } while (0 < $num);

        return $b26;
    }

    private function numToRoman(int $integer): string
    {
        $result = '';

        $lookup = [
            'm'  => 1000,
            'cm' => 900,
            'd'  => 500,
            'cd' => 400,
            'c'  => 100,
            'xc' => 90,
            'l'  => 50,
            'xl' => 40,
            'x'  => 10,
            'ix' => 9,
            'v'  => 5,
            'iv' => 4,
            'i'  => 1
        ];

        foreach ($lookup as $roman => $value) {
            $result .= str_repeat($roman, (int) ($integer / $value));

            // Set the integer to be the remainder of the integer and the value
            $integer = $integer % $value;
        }

        // The Roman numeral should be built, return it
        return $result;
    }
}
