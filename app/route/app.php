<?php

namespace app\route\Fault;

use think\facade\Route;

Route::rule('/', 'index');
Route::get('/fault', 'Fault/index');
Route::rule('/login', 'login/index');
