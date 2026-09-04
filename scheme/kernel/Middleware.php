<?php
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

/**
* ------------------------------------------------------
*  Class Middleware
* ------------------------------------------------------
 */
class Middleware
{
    /**
     * Map
     *
     * @var array
     */
    protected $map = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $config = get_config();

        if (!isset($config['middlewares'])) {
            throw new RuntimeException('Middleware config not found.');
        }

        $this->map = $config['middlewares'];
    }

    /**
     * Run the middleware pipeline
     *
     * @param array $middlewares
     * @param Closure $destination
     * @return mixed
     */
    public function run(array $middlewares, Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function () use ($middleware, $next) {
                    return $this->resolve($middleware, $next);
                };
            },
            $destination
        );

        return $pipeline();
    }

    /**
     * Resolve a middleware
     *
     * @param string $middleware
     * @param Closure $next
     * @return mixed
     */
    protected function resolve($middleware, $next)
    {
        if (!isset($this->map[$middleware])) {
            throw new Exception("Middleware [$middleware] not registered.");
        }

        return $this->map[$middleware]->handle($next);
    }
}

