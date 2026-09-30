<?php

use App\Support\RoomCode;

/*
|--------------------------------------------------------------------------
| RoomCode
|--------------------------------------------------------------------------
|
| A true unit test: no Laravel, no database. RoomCode is plain PHP, so it's
| created with `new` and tested directly.
|
*/

test('generated codes are four letters from the allowed alphabet', function () {
    $codes = new RoomCode;

    // Codes are random, so check a large sample instead of a single one.
    for ($i = 0; $i < 500; $i++) {
        expect($codes->generate())->toMatch('/^['.RoomCode::ALPHABET.']{4}$/');
    }
});

test('the alphabet leaves out letters that look like numbers', function () {
    expect(RoomCode::ALPHABET)
        ->not->toContain('I')
        ->not->toContain('O');
});

test('typed codes are normalized', function (string $typed, string $expected) {
    expect(RoomCode::normalize($typed))->toBe($expected);
})->with([
    'already normalized' => ['ABCD', 'ABCD'],
    'lowercase' => ['abcd', 'ABCD'],
    'mixed case' => ['aBcD', 'ABCD'],
    'surrounding spaces' => ['  ABCD ', 'ABCD'],
    'tabs and newlines' => ["\tabcd\n", 'ABCD'],
]);
