<?php

test('health check endpoint responds successfully', function () {
    $this->get('/up')->assertOk();
});
