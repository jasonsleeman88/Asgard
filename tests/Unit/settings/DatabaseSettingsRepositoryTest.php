<?php

use App\Settings\Repositories\DatabaseSettings;
use Illuminate\Database\ConnectionInterface;

beforeEach(function () {
    $connection = Mockery::mock(ConnectionInterface::class);
    $repository = new DatabaseSettings($connection);
});

test('requesting an existing setting should return its value', function () {
    $connection = Mockery::mock(ConnectionInterface::class);
    $repository = new DatabaseSettings($connection);

    $connection->shouldReceive('table->where->value')->andReturn('value');
    $this->assertEquals('value', $repository->get('key'));
});

test('non_existent_setting_values_should_return_null', function () {
    $connection = Mockery::mock(ConnectionInterface::class);
    $repository = new DatabaseSettings($connection);

    $connection->shouldReceive('table->where->value')->andReturn(null);
    $this->assertEquals('default', $repository->get('key', 'default'));
});
