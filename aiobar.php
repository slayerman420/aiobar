<?php
/**
 * Plugin Name:  AIObar
 * Description:  Adds a social sharing + AI citation toolbar to single posts. Get your content into AI answers. Zero external dependencies.
 * Version:      1.0.0
 * License:      GPL-2.0+
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Operator-configurable constants (override in wp-config.php)
// ---------------------------------------------------------------------------
if ( ! defined( 'AIOBAR_PRIMARY_COLOR' ) )   define( 'AIOBAR_PRIMARY_COLOR',   '#000000' );
if ( ! defined( 'AIOBAR_PRIMARY_HOVER' ) )   define( 'AIOBAR_PRIMARY_HOVER',   '#222222' );
if ( ! defined( 'AIOBAR_BORDER_COLOR' ) )    define( 'AIOBAR_BORDER_COLOR',    '#E5E5E5' );
if ( ! defined( 'AIOBAR_TEXT_COLOR' ) )      define( 'AIOBAR_TEXT_COLOR',      '#555555' );
if ( ! defined( 'AIOBAR_FONT_FAMILY' ) )     define( 'AIOBAR_FONT_FAMILY',     'system-ui, sans-serif' );
if ( ! defined( 'AIOBAR_BORDER_RADIUS' ) )   define( 'AIOBAR_BORDER_RADIUS',   '8px' );

// Subdomain/proxy URL rewriting (see README for explanation)
if ( ! defined( 'PUBLIC_BASE_URL' ) ) define( 'PUBLIC_BASE_URL', '' );

// ---------------------------------------------------------------------------
// URL helper: rewrites WordPress permalink to public-facing URL when needed
// ---------------------------------------------------------------------------
function aiobar_public_url() {
	$wp_url = get_permalink();
	if ( PUBLIC_BASE_URL === '' ) {
		return $wp_url;
	}
	$parsed = parse_url( $wp_url );
	$path   = isset( $parsed['path'] ) ? $parsed['path'] : '/';
	return rtrim( PUBLIC_BASE_URL, '/' ) . '/' . ltrim( $path, '/' );
}

// ---------------------------------------------------------------------------
// Enqueue assets — JS in footer, CSS via inline style
// ---------------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', 'aiobar_enqueue_assets' );

function aiobar_enqueue_assets() {
	if ( ! is_single() ) {
		return;
	}

	// ---- CSS (inline, zero HTTP requests) ---------------------------------
	$primary   = esc_attr( AIOBAR_PRIMARY_COLOR );
	$hover     = esc_attr( AIOBAR_PRIMARY_HOVER );
	$border    = esc_attr( AIOBAR_BORDER_COLOR );
	$text      = esc_attr( AIOBAR_TEXT_COLOR );
	$font      = esc_attr( AIOBAR_FONT_FAMILY );
	$radius    = esc_attr( AIOBAR_BORDER_RADIUS );

	$css = "
/* ── AIObar ── */
.aiobar-toolbar {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
	padding: 12px 0;
	margin-bottom: 24px;
	border-bottom: 1px solid {$border};
	font-family: {$font};
	position: relative;
	box-sizing: border-box;
}
.aiobar-social-group {
	display: flex;
	align-items: center;
	gap: 8px;
	flex: 0 0 auto;
}
.aiobar-action-group {
	display: flex;
	align-items: center;
	gap: 8px;
	flex: 1 1 auto;
	justify-content: flex-end;
	flex-wrap: wrap;
}

/* Social icon buttons */
.aiobar-social-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	border: 1px solid {$border};
	border-radius: {$radius};
	background: transparent;
	cursor: pointer;
	padding: 0;
	transition: background 0.15s, border-color 0.15s;
	text-decoration: none;
	box-sizing: border-box;
}
.aiobar-social-btn svg {
	width: 18px;
	height: 18px;
	display: block;
	transition: fill 0.15s, stroke 0.15s;
}
.aiobar-social-btn:hover {
	background: {$primary};
	border-color: {$primary};
}
.aiobar-social-btn:hover svg path,
.aiobar-social-btn:hover svg rect,
.aiobar-social-btn:hover svg circle,
.aiobar-social-btn:hover svg polygon {
	fill: #ffffff !important;
	stroke: #ffffff !important;
}

/* Action buttons */
.aiobar-action-btn {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	border: 1px solid {$border};
	border-radius: {$radius};
	background: transparent;
	color: {$text};
	font-family: {$font};
	font-size: 13px;
	padding: 8px 14px;
	cursor: pointer;
	transition: border-color 0.15s, background 0.15s, color 0.15s;
	white-space: nowrap;
	line-height: 1;
	text-decoration: none;
}
.aiobar-action-btn:hover {
	border-color: {$primary};
	color: {$text};
}
.aiobar-action-btn.aiobar-active {
	background: {$primary};
	border-color: {$primary};
	color: #ffffff;
}

/* Summarize wrapper — needed for absolute positioning of dropdown */
.aiobar-summarize-wrap {
	position: relative;
}

/* Dropdown */
.aiobar-summarize-dropdown {
	position: absolute;
	top: calc(100% + 4px);
	left: 0;
	min-width: 160px;
	background: #ffffff;
	border: 1px solid {$border};
	border-radius: {$radius};
	box-shadow: 0 4px 12px rgba(0,0,0,0.08);
	z-index: 999;
	overflow: hidden;
}
.aiobar-summarize-dropdown button {
	display: block;
	width: 100%;
	text-align: left;
	background: transparent;
	border: none;
	font-family: {$font};
	font-size: 13px;
	color: {$text};
	padding: 10px 16px;
	cursor: pointer;
	transition: background 0.1s;
	box-sizing: border-box;
}
.aiobar-summarize-dropdown button:hover {
	background: #f5f5f5;
}

/* Markdown panel */
.aiobar-markdown-panel {
	width: 100%;
	margin-top: 8px;
	background: #1a1a1a;
	color: #e5e5e5;
	font-family: monospace;
	font-size: 13px;
	padding: 16px;
	border-radius: {$radius};
	overflow-x: auto;
	max-height: 400px;
	overflow-y: auto;
	box-sizing: border-box;
}
.aiobar-markdown-panel pre {
	margin: 0;
	padding: 0;
	white-space: pre;
	font-family: inherit;
	font-size: inherit;
	color: inherit;
	background: none;
	border: none;
}
.aiobar-markdown-panel code {
	font-family: inherit;
	font-size: inherit;
	color: inherit;
	background: none;
}

/* Mobile layout */
@media (max-width: 640px) {
	.aiobar-toolbar {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px;
	}
	.aiobar-social-group {
		grid-column: 1 / -1;
		flex-wrap: wrap;
	}
	.aiobar-action-group {
		grid-column: 1 / -1;
		display: grid;
		grid-template-columns: 1fr 1fr;
		justify-content: initial;
	}
	.aiobar-summarize-wrap {
		grid-column: 1 / -1;
	}
	.aiobar-markdown-panel {
		grid-column: 1 / -1;
	}
}
";

	wp_register_style( 'aiobar', false );
	wp_enqueue_style( 'aiobar' );
	wp_add_inline_style( 'aiobar', $css );

	// ---- JS — loaded in footer (last param = true) -----------------------
	wp_register_script(
		'aiobar',
		false,         // no external file
		array(),
		'1.0.0',
		true           // footer
	);
	wp_enqueue_script( 'aiobar' );

	// Pass post data to JS safely — no inline vars in HTML output
	$post_title = get_the_title();
	$post_url   = aiobar_public_url();

	// Strip tags from post body to get plain text for "Copy for LLM"
	global $post;
	$raw_content  = isset( $post->post_content ) ? $post->post_content : '';
	$applied      = apply_filters( 'the_content', $raw_content );
	$body_text    = wp_strip_all_tags( $applied );

	$data = array(
		'postTitle' => $post_title,
		'postUrl'   => $post_url,
		'bodyText'  => $body_text,
	);

	wp_add_inline_script(
		'aiobar',
		'var AIObarData = ' . wp_json_encode( $data ) . ';',
		'before'
	);

	// The main JS logic
	$js = aiobar_get_js();
	wp_add_inline_script( 'aiobar', $js );
}

// ---------------------------------------------------------------------------
// Inject toolbar HTML above post content
// ---------------------------------------------------------------------------
add_filter( 'the_content', 'aiobar_inject_toolbar', 10, 1 );

function aiobar_inject_toolbar( $content ) {
	if ( ! is_single() ) {
		return $content;
	}

	$post_title  = esc_attr( get_the_title() );
	$post_url    = esc_url( aiobar_public_url() );

	// Build share URLs (encoding done server-side for the href attributes;
	// JS handles the dynamic ones via AIObarData)
	$fb_url   = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( aiobar_public_url() );
	$li_url   = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( aiobar_public_url() );
	$x_url    = 'https://twitter.com/intent/tweet?url=' . rawurlencode( aiobar_public_url() ) . '&text=' . rawurlencode( get_the_title() );
	$rd_url   = 'https://reddit.com/submit?url=' . rawurlencode( aiobar_public_url() ) . '&title=' . rawurlencode( get_the_title() );

	// SVG icons
	$svg_facebook  = aiobar_svg_facebook();
	$svg_linkedin  = aiobar_svg_linkedin();
	$svg_x         = aiobar_svg_x();
	$svg_reddit    = aiobar_svg_reddit();
	$svg_summarize = aiobar_svg_summarize();
	$svg_copy      = aiobar_svg_copy();
	$svg_markdown  = aiobar_svg_markdown();

	$toolbar = '
<div class="aiobar-toolbar" aria-label="AIObar">

	<!-- Social share group -->
	<div class="aiobar-social-group">
		<a href="' . esc_url( $fb_url ) . '" target="_blank" rel="noopener noreferrer"
		   class="aiobar-social-btn" aria-label="Share on Facebook"
		   data-aiobar-action="share-facebook">
			' . $svg_facebook . '
		</a>
		<a href="' . esc_url( $li_url ) . '" target="_blank" rel="noopener noreferrer"
		   class="aiobar-social-btn" aria-label="Share on LinkedIn"
		   data-aiobar-action="share-linkedin">
			' . $svg_linkedin . '
		</a>
		<a href="' . esc_url( $x_url ) . '" target="_blank" rel="noopener noreferrer"
		   class="aiobar-social-btn" aria-label="Share on X"
		   data-aiobar-action="share-x">
			' . $svg_x . '
		</a>
		<a href="' . esc_url( $rd_url ) . '" target="_blank" rel="noopener noreferrer"
		   class="aiobar-social-btn" aria-label="Share on Reddit"
		   data-aiobar-action="share-reddit">
			' . $svg_reddit . '
		</a>
	</div>

	<!-- Action buttons group -->
	<div class="aiobar-action-group">

		<!-- Summarize with dropdown -->
		<div class="aiobar-summarize-wrap">
			<button class="aiobar-action-btn" data-aiobar-action="summarize-toggle" aria-haspopup="true" aria-expanded="false">
				' . $svg_summarize . ' Summarize
			</button>
			<div class="aiobar-summarize-dropdown" style="display:none;" role="menu">
				<button data-aiobar-action="summarize-claude"     role="menuitem">Claude</button>
				<button data-aiobar-action="summarize-chatgpt"    role="menuitem">ChatGPT</button>
				<button data-aiobar-action="summarize-perplexity" role="menuitem">Perplexity</button>
			</div>
		</div>

		<!-- Copy for LLM -->
		<button class="aiobar-action-btn" data-aiobar-action="copy-llm" aria-label="Copy post content for LLM">
			' . $svg_copy . ' <span class="aiobar-copy-label">Copy for LLM</span>
		</button>

		<!-- View Markdown -->
		<button class="aiobar-action-btn" data-aiobar-action="view-markdown" aria-expanded="false">
			' . $svg_markdown . ' <span class="aiobar-md-label">View Markdown</span>
		</button>

	</div><!-- /.aiobar-action-group -->

	<!-- Markdown panel (toggled by JS) -->
	<div class="aiobar-markdown-panel" style="display:none;" aria-live="polite">
		<pre><code class="aiobar-markdown-content"></code></pre>
	</div>

</div><!-- /.aiobar-toolbar -->
';

	return $toolbar . $content;
}

// ---------------------------------------------------------------------------
// JavaScript — returned as a string, enqueued via wp_add_inline_script()
// ---------------------------------------------------------------------------
function aiobar_get_js() {
	return <<<'JS'
(function () {
	'use strict';

	// ── Helpers ────────────────────────────────────────────────────────────

	function enc(str) {
		return encodeURIComponent(str);
	}

	function summarizePrompt() {
		return 'Please summarise this article for me: ' +
			AIObarData.postTitle + ' \u2014 ' + AIObarData.postUrl;
	}

	function openTab(url) {
		window.open(url, '_blank', 'noopener,noreferrer');
	}

	function getDropdown() {
		return document.querySelector('.aiobar-summarize-dropdown');
	}

	function getSummarizeBtn() {
		return document.querySelector('[data-aiobar-action="summarize-toggle"]');
	}

	function closeDropdown() {
		var dd  = getDropdown();
		var btn = getSummarizeBtn();
		if (!dd) return;
		dd.style.display = 'none';
		if (btn) {
			btn.classList.remove('aiobar-active');
			btn.setAttribute('aria-expanded', 'false');
		}
	}

	// ── Event delegation ───────────────────────────────────────────────────

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-aiobar-action]');

		// Click outside — close dropdown
		if (!btn || btn.dataset.aiobarAction !== 'summarize-toggle') {
			var dd = getDropdown();
			if (dd && dd.style.display !== 'none') {
				if (!btn || !btn.dataset.aiobarAction || btn.dataset.aiobarAction.indexOf('summarize-') !== 0) {
					closeDropdown();
				}
			}
		}

		if (!btn) return;

		var action = btn.dataset.aiobarAction;

		switch (action) {

			// ── Social sharing ─────────────────────────────────────────
			case 'share-facebook':
			case 'share-linkedin':
			case 'share-x':
			case 'share-reddit':
				// Links already have href; let the browser handle them.
				// This branch just prevents the switch from falling through.
				break;

			// ── Summarize toggle ───────────────────────────────────────
			case 'summarize-toggle': {
				e.preventDefault();
				var dd2 = getDropdown();
				if (!dd2) break;
				var isOpen = dd2.style.display !== 'none';
				if (isOpen) {
					closeDropdown();
				} else {
					dd2.style.display = 'block';
					btn.classList.add('aiobar-active');
					btn.setAttribute('aria-expanded', 'true');
				}
				break;
			}

			// ── Summarize destinations ─────────────────────────────────
			case 'summarize-claude':
				e.preventDefault();
				openTab('https://claude.ai/new?q=' + enc(summarizePrompt()));
				closeDropdown();
				break;

			case 'summarize-chatgpt':
				e.preventDefault();
				openTab('https://chatgpt.com/?q=' + enc(summarizePrompt()));
				closeDropdown();
				break;

			case 'summarize-perplexity':
				e.preventDefault();
				openTab('https://www.perplexity.ai/?q=' + enc(summarizePrompt()));
				closeDropdown();
				break;

			// ── Copy for LLM ───────────────────────────────────────────
			case 'copy-llm': {
				e.preventDefault();
				var text = 'Title: ' + AIObarData.postTitle +
					'\nURL: ' + AIObarData.postUrl +
					'\n\n' + AIObarData.bodyText;

				var labelEl = btn.querySelector('.aiobar-copy-label');

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(function () {
						if (labelEl) labelEl.textContent = 'Copied!';
						setTimeout(function () {
							if (labelEl) labelEl.textContent = 'Copy for LLM';
						}, 2000);
					}).catch(function () {
						aiobarFallbackCopy(text, labelEl);
					});
				} else {
					aiobarFallbackCopy(text, labelEl);
				}
				break;
			}

			// ── View Markdown ──────────────────────────────────────────
			case 'view-markdown': {
				e.preventDefault();
				var panel   = document.querySelector('.aiobar-markdown-panel');
				var codeEl  = document.querySelector('.aiobar-markdown-content');
				var labelMd = btn.querySelector('.aiobar-md-label');
				if (!panel) break;

				var isVisible = panel.style.display !== 'none';

				if (isVisible) {
					panel.style.display = 'none';
					btn.setAttribute('aria-expanded', 'false');
					if (labelMd) labelMd.textContent = 'View Markdown';
				} else {
					if (codeEl && !codeEl.dataset.populated) {
						codeEl.textContent = AIObarData.bodyText;
						codeEl.dataset.populated = '1';
					}
					panel.style.display = 'block';
					btn.setAttribute('aria-expanded', 'true');
					if (labelMd) labelMd.textContent = 'Hide Markdown';
				}
				break;
			}
		}
	});

	// ── Fallback clipboard for non-HTTPS or older browsers ─────────────────
	function aiobarFallbackCopy(text, labelEl) {
		try {
			var ta = document.createElement('textarea');
			ta.value = text;
			ta.style.position = 'fixed';
			ta.style.top = '0';
			ta.style.left = '0';
			ta.style.opacity = '0';
			document.body.appendChild(ta);
			ta.focus();
			ta.select();
			document.execCommand('copy');
			document.body.removeChild(ta);
			if (labelEl) {
				labelEl.textContent = 'Copied!';
				setTimeout(function () {
					labelEl.textContent = 'Copy for LLM';
				}, 2000);
			}
		} catch (err) {
			if (labelEl) labelEl.textContent = 'Failed';
			setTimeout(function () {
				if (labelEl) labelEl.textContent = 'Copy for LLM';
			}, 2000);
		}
	}

})();
JS;
}

// ---------------------------------------------------------------------------
// Inline SVG icons (self-contained, no external assets)
// ---------------------------------------------------------------------------

function aiobar_svg_facebook() {
	return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"
		      fill="#555555" stroke="none"/>
	</svg>';
}

function aiobar_svg_linkedin() {
	return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"
		      fill="#555555" stroke="none"/>
		<rect x="2" y="9" width="4" height="12" fill="#555555" stroke="none"/>
		<circle cx="4" cy="4" r="2" fill="#555555" stroke="none"/>
	</svg>';
}

function aiobar_svg_x() {
	return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
		      fill="#555555" stroke="none"/>
	</svg>';
}

function aiobar_svg_reddit() {
	return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<circle cx="12" cy="12" r="10" fill="#555555" stroke="none"/>
		<path d="M20 12a2 2 0 0 0-2-2 1.9 1.9 0 0 0-1.29.48 9.85 9.85 0 0 0-5.29-1.68l.9-4.2 2.93.62a1.5 1.5 0 1 0 .15-.73l-3.27-.69a.25.25 0 0 0-.3.19l-1 4.68a9.87 9.87 0 0 0-5.33 1.67A1.9 1.9 0 0 0 4 10a2 2 0 0 0-.7 3.87 3.5 3.5 0 0 0 0 .42c0 2.16 2.52 3.91 5.63 3.91S14.9 16.45 14.9 14.29a3.5 3.5 0 0 0 0-.42A2 2 0 0 0 20 12zm-12.5 1a1 1 0 1 1 1 1 1 1 0 0 1-1-1zm5.64 2.8a3.53 3.53 0 0 1-2.14.57 3.53 3.53 0 0 1-2.14-.57.25.25 0 0 1 .3-.4 3.07 3.07 0 0 0 1.84.47 3.07 3.07 0 0 0 1.84-.47.25.25 0 0 1 .3.4zm-.14-1.8a1 1 0 1 1 1-1 1 1 0 0 1-1 1z"
		      fill="#ffffff" stroke="none"/>
	</svg>';
}

function aiobar_svg_summarize() {
	return '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="width:14px;height:14px;">
		<path d="M2 4h12M2 7h8M2 10h10M2 13h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
	</svg>';
}

function aiobar_svg_copy() {
	return '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="width:14px;height:14px;">
		<rect x="5" y="5" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
		<path d="M11 5V3.5A1.5 1.5 0 0 0 9.5 2h-6A1.5 1.5 0 0 0 2 3.5v6A1.5 1.5 0 0 0 3.5 11H5" stroke="currentColor" stroke-width="1.5"/>
	</svg>';
}

function aiobar_svg_markdown() {
	return '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="width:14px;height:14px;">
		<rect x="1" y="3" width="14" height="10" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
		<path d="M4 10V6l2.5 3L9 6v4M11 10l2-2-2-2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>';
}
