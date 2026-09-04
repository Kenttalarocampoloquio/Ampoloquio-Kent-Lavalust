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

/**
 * @var \Exception|\Throwable $exception
 * @var string $heading
 * @var string $message
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>404 · Page not found</title>

<style nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
:root {
  --bg-body: #0a0c10;
  --bg-surface: #14161f;
  --bg-elevated: #1a1d2b;
  --border-subtle: #2a2e3d;
  --text-primary: #eef2ff;
  --text-secondary: #9ca3c7;
  --text-muted: #6b728c;
  --accent-primary: #dd4814;
  --accent-glow: rgba(221,72,20,0.25);
  --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
  --font-sans: system-ui, -apple-system, 'Segoe UI', 'Inter', 'Roboto', sans-serif;
  --font-mono: 'SF Mono', 'Fira Code', monospace;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

html, body { height: 100%; }

body {
  background: var(--bg-body);
  font-family: var(--font-sans);
  color: var(--text-primary);
  display: grid;
  place-items: center;
  padding: 1.5rem;
  line-height: 1.5;
}

.card {
  max-width: 480px;
  width: 100%;
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 2rem;
  box-shadow: var(--shadow-md);
  padding: 2rem;
  backdrop-filter: blur(2px);
  transition: transform 0.2s ease;
}

.card:hover {
  transform: translateY(-2px);
}

.badge {
  font-family: var(--font-mono);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--accent-primary);
  background: rgba(221,72,20,0.12);
  border: 1px solid rgba(221,72,20,0.25);
  border-radius: 40px;
  padding: 0.25rem 0.8rem;
  display: inline-block;
  margin-bottom: 1rem;
  letter-spacing: -0.2px;
}

h1 {
  font-size: 1.75rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  background: linear-gradient(135deg, #f0f3ff, #cbd5f0);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  letter-spacing: -0.3px;
}

.message {
  font-size: 0.95rem;
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

.actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}

.btn {
  padding: 0.6rem 1.2rem;
  font-size: 0.85rem;
  font-weight: 500;
  border-radius: 40px;
  text-decoration: none;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: var(--accent-primary);
  color: white;
  border: none;
  box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.btn-primary:hover {
  background: #b83c0f;
  transform: scale(0.98);
  box-shadow: 0 2px 5px rgba(221,72,20,0.3);
}

.btn-secondary {
  background: transparent;
  border: 1px solid var(--border-subtle);
  color: var(--text-secondary);
}

.btn-secondary:hover {
  background: rgba(255,255,255,0.05);
  border-color: var(--text-muted);
  color: var(--text-primary);
}

.hint {
  font-size: 0.75rem;
  color: var(--text-muted);
  border-top: 1px dashed var(--border-subtle);
  padding-top: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.kbd {
  font-family: var(--font-mono);
  background: rgba(156, 163, 199, 0.15);
  border: 1px solid rgba(156, 163, 199, 0.3);
  border-radius: 6px;
  padding: 0.2rem 0.5rem;
  font-size: 0.7rem;
  font-weight: 500;
  color: var(--text-secondary);
}

@media (max-width: 480px) {
  .card { padding: 1.5rem; }
  h1 { font-size: 1.5rem; }
  .actions { flex-direction: column; }
  .btn { justify-content: center; }
}
</style>
</head>

<body>
<main class="card" role="main">
  <div class="badge">404 · Not Found</div>
  <h1><?= html_escape($heading) ?></h1>
  <div class="message"><?= html_escape($message) ?></div>

  <div class="actions">
    <a class="btn btn-primary" href="/">Home</a>
    <a class="btn btn-secondary" href="javascript:history.back()">← Go Back</a>
  </div>

  <div class="hint">
    <span>Tip:</span>
    <span class="kbd">Ctrl</span> + <span class="kbd">L</span>
    <span>to focus the address bar and retype the URL.</span>
  </div>
</main>
</body>
</html>