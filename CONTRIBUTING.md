# Contributing

Thank you for considering a contribution to Blog Content Toolbar!

## Reporting Issues

Open an [issue](https://github.com/slayerman420/blog-content-toolbar/issues) and include:
- WordPress version
- PHP version
- Active theme name
- Any active caching plugins
- Steps to reproduce

## Pull Requests

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-change`
3. Make your changes in `blog-content-toolbar.php`
4. Test on at least one clean WordPress install
5. Open a PR with a clear description of what changed and why

## Code Style

- Follow WordPress Coding Standards for PHP (tabs for indentation, Yoda conditions)
- Keep all JS in ES5 — no arrow functions, no `const`/`let`, no template literals
- Prefix all PHP functions and constants with `bctb_` / `BCTB_`
- Prefix all CSS classes with `bctb-`
- Zero external dependencies — no CDN calls, no npm packages

## Architecture Rules (do not break these)

- All JS must be enqueued via `wp_enqueue_script()` — never echoed inside `the_content`
- All JS data passed via `wp_add_inline_script()` — never echoed as HTML attributes
- Event listeners use document-level delegation only
- Toolbar guard condition is `is_single()` only

## License

By contributing you agree your code will be licensed under GPL-2.0+.
