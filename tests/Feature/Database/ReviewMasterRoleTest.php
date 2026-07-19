<?php

test('the review master role migration is applied', function () {
    $this->assertDatabaseHas('roles', [
        'id' => 3,
        'name' => 'Review Master',
        'slug' => 'teacher',
    ]);
});

test('the review_masters table exists', function () {
    $this->assertDatabaseCount('review_masters', 0);
});
