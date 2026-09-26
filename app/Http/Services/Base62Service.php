<?php

namespace App\Http\Services;

class Base62Service
{
    private const ALPHABET = 'k9wM1ZaT5XyvGgYnU0K2qVzB6iL4cR7eP8xWb3uJpmsSdNtHjQfCDoEAFlrhmI';
    private const BASE = 62;
    // Смещение, чтобы первый ID не давал код из одной буквы вроде "b"
    private const OFFSET = 10_000_000;

    public function encode(int $id): string
    {
        $id += self::OFFSET;
        $code = '';

        while ($id > 0) {
            $remainder = $id % self::BASE;
            $code = self::ALPHABET[$remainder] . $code;
            $id = intdiv($id, self::BASE);
        }

        return $code;
    }

    public function decode(string $code): int
    {
        $id = 0;
        $len = strlen($code);

        for ($i = 0; $i < $len; $i++) {
            $char = $code[$i];
            $pos = strpos(self::ALPHABET, $char);
            if ($pos === false) {
                throw new \InvalidArgumentException("Invalid character: {$char}");
            }
            $id = ($id * self::BASE) + $pos;
        }

        return $id - self::OFFSET;
    }
}
