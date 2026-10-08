<?php

test('pengunjung anonim diarahkan ke login dari halaman utama', function () {
    $this->get('/')->assertRedirect('/dashboard');
    $this->get('/dashboard')->assertRedirect('/login');
});
