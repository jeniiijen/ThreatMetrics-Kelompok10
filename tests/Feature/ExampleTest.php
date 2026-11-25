<?php

test('the home page redirects guests to login', function () {
    $this->get('/')->assertRedirectToRoute('login');
});
