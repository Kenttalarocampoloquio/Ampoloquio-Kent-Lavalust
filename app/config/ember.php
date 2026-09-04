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
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
|--------------------------------------------------------------------------
| Enable/Disable Ember Helper
|--------------------------------------------------------------------------
|
| Used for Enabling or Disabling Ember Helper
|
*/
$config['ember_helper_enabled'] = FALSE;

/*
|--------------------------------------------------------------------------
| Cache Path
|--------------------------------------------------------------------------
|
| Used for Storing Cached Templates
|
*/
$config['cache_path'] = ROOT_DIR . 'runtime/cache/';

/*
|--------------------------------------------------------------------------
| Views Path
|--------------------------------------------------------------------------
|
| Used for Storing View Templates
|
*/
$config['templates_path'] = APP_DIR . 'views/';

/*
|-------------------------------------------------------------------------- 
| Auto Escape
|--------------------------------------------------------------------------     
| Used for Enabling or Disabling Auto Escape Output
|
*/
$config['auto_escape'] = TRUE;

/*
|--------------------------------------------------------------------------
| Escape Context
|--------------------------------------------------------------------------
| Used for Specifying the Default Context for Auto Escaping (e.g., 'html', 'js', 'attr')
|
*/
$config['escape_context']     = 'html';

/*|--------------------------------------------------------------------------
| Enable PHP in Templates
|--------------------------------------------------------------------------
| Used for Allowing or Disallowing Raw PHP Code in Templates (strongly recommended to keep false for security)
|
*/
$config['enable_php_blocks']  = FALSE;