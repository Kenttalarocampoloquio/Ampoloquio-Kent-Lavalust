<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/
/** @var object $router **/

$router->get('/', 'Welcome::index');
(function () {
    require APP_DIR . 'config/middleware.php';
    get_config($config);
})();
$router->get('/login', 'AuthController::login');
$router->post('/login', 'AuthController::login');
$router->get('/logout', 'AuthController::logout');

$router->get('/products', 'ProductController::index')->middleware('auth');
$router->get('/products/create', 'ProductController::create')->middleware('auth');
$router->post('/products/create', 'ProductController::create')->middleware('auth');
$router->get('/products/edit', 'ProductController::edit')->middleware('auth');
$router->post('/products/edit', 'ProductController::edit')->middleware('auth');
$router->get('/products/delete', 'ProductController::delete')->middleware('auth');

$router->get('create-migration/{migration_class}', 'MigrationController::create_migration');
$router->get('migrate', 'MigrationController::migrate');
$router->get('rollback', 'MigrationController::rollback');
$router->get('rollback-all', 'MigrationController::rollback_all');
$router->get('refresh', 'MigrationController::refresh');
$router->get('status', 'MigrationController::status');
// ---------------------------------------------------------------
// JSON API (consumed by the React/Vue frontend)
// OPTIONS is included so CORS preflight requests succeed.
// ---------------------------------------------------------------
$router->match('api/register', 'ApiAuthController::register', ['POST', 'OPTIONS']);
$router->match('api/login',    'ApiAuthController::login',    ['POST', 'OPTIONS']);
$router->match('api/refresh',  'ApiAuthController::refresh',  ['POST', 'OPTIONS']);
$router->match('api/logout',   'ApiAuthController::logout',   ['POST', 'OPTIONS']);

$router->match('api/products',        'ApiProductController::index',  ['GET', 'OPTIONS']);
$router->match('api/products',        'ApiProductController::store',  ['POST', 'OPTIONS']);
$router->match('api/products/{id}',   'ApiProductController::show',   ['GET', 'OPTIONS']);
$router->match('api/products/{id}',   'ApiProductController::update', ['PUT', 'PATCH', 'OPTIONS']);
$router->match('api/products/{id}',   'ApiProductController::destroy',['DELETE', 'OPTIONS']);
