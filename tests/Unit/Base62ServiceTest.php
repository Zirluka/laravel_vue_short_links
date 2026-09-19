<?php

use App\Http\Services\Base62Service;

test('it accurately encodes and decodes integer ids', function () {
    $service = new Base62Service();

    $testIds = [1, 42, 100, 1000, 999999, 12345678];

    foreach ($testIds as $id) {
        $encoded = $service->encode($id);

        // Код должен состоять исключительно из символов 0-9, a-z, A-Z
        expect($encoded)->toMatch('/^[0-9a-zA-Z]+$/');

        // Обратная расшифровка обязана вернуть исходный ID
        $decoded = $service->decode($encoded);
        expect($decoded)->toBe($id);
    }
});

test('subsequent ids generate unique codes', function () {
    $service = new Base62Service();

    $code1 = $service->encode(100);
    $code2 = $service->encode(101);

    expect($code1)->not->toBe($code2);
});

test('it throws exception when decoding invalid characters', function () {
    $service = new Base62Service();

    // Символы '-', '_', '!' не входят в алфавит Base62
    $service->decode('abc-123');
})->throws(\InvalidArgumentException::class);
