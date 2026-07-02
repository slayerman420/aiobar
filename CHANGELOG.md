# Changelog

All notable changes to this project will be documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.0.0] — 2025-07-02

### Added
- Social share buttons: Facebook, LinkedIn, X (Twitter), Reddit
- Summarize dropdown with Claude, ChatGPT, Perplexity destinations
- Pre-filled summarise prompt using post title and URL
- Copy for LLM — assembles Title + URL + plain-text body to clipboard
- Fallback `execCommand` copy for non-HTTPS / older browsers
- View Markdown collapsible panel with dark monospace styling
- All visual design configurable via PHP constants
- `PUBLIC_BASE_URL` constant for subdomain/reverse-proxy URL rewriting
- Desktop flexbox layout (social left, actions right)
- Mobile CSS Grid 2-column layout
- All JS via `wp_enqueue_script()` in footer — no inline `<script>` in content
- Document-level event delegation — no direct element listeners
- `is_single()` guard — correct in all WordPress deployment topologies
- Zero external dependencies
- Full README with installation, configuration, theming, and caching guide
