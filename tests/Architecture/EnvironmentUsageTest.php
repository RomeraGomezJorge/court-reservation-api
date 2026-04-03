<?php


declare(strict_types=1);

it('ensures no files outside the config directory use the env() helper', function (): void {
    expect('App')->not->toUse(['env']);
});
