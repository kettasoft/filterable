<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";

const root = ref(null);
const activeEngine = ref("invokable");
const copied = ref(false);
const hasScrolled = ref(false);
let copyTimer;
let observer;

const engines = [
    {
        id: "invokable",
        index: "01",
        name: "Invokable",
        label: "Method-driven",
        description:
            "Map request keys to focused PHP methods and enrich them with attributes.",
        request: "?status=published&search=laravel",
        href: "./engines/invokable/",
    },
    {
        id: "ruleset",
        index: "02",
        name: "Ruleset",
        label: "API-friendly",
        description:
            "Expose an explicit field/operator/value contract for predictable REST APIs.",
        request: "?filter[views][gte]=100",
        href: "./engines/rule-set.html",
    },
    {
        id: "expression",
        index: "03",
        name: "Expression",
        label: "Relation-aware",
        description:
            "Filter direct and deeply nested Eloquent relations with clear allowlists.",
        request: "?filter[author.name][like]=ahmed",
        href: "./engines/expression.html",
    },
    {
        id: "tree",
        index: "04",
        name: "Tree",
        label: "Logic-rich",
        description:
            "Translate nested AND/OR JSON trees into correctly grouped Eloquent queries.",
        request: '{ "and": [{ "field": "status" }] }',
        href: "./engines/tree.html",
    },
];

const examples = {
    invokable: `class PostFilter extends Filterable
{
    protected $filters = ['status', 'search'];

    #[Trim]
    protected function search(Payload $payload): Builder
    {
        return $this->builder->where(
            'title', 'like', $payload->asLike('both')
        );
    }
}`,
    ruleset: `Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->setAllowedFields(['status', 'views', 'created_at'])
    ->allowedOperators(['eq', 'gte', 'between'])
    ->latest()
    ->paginate();`,
    expression: `Filterable::for(Post::class, $request)
    ->using('expression')
    ->setAllowedFields(['status', 'title'])
    ->allowRelations([
        'author.profile' => ['name'],
        'tags' => ['name'],
    ])
    ->paginate();`,
    tree: `POST /api/posts

{
  "filter": {
    "and": [
      { "field": "status", "operator": "eq", "value": "active" },
      { "or": [
        { "field": "views", "operator": "gte", "value": 100 },
        { "field": "featured", "operator": "eq", "value": true }
      ]}
    ]
  }
}`,
};

const activeCode = computed(() => examples[activeEngine.value]);
const highlightedCode = computed(() => highlightCode(activeCode.value));

function escapeHtml(value) {
    return value
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;");
}

function highlightCode(code) {
    const tokens =
        /(#\[[A-Za-z]+(?:\([^)]*\))?\])|('(?:\\'|[^'])*'|"(?:\\"|[^"])*")|(\/\/.*$)|(\$[A-Za-z_]\w*)|\b(final|class|extends|protected|function|return|true|false|null)\b|\b(Filterable|Payload|Builder|Post)\b|(->|::|=>)|(\b\d+\b)/gm;
    let output = "";
    let offset = 0;

    for (const match of code.matchAll(tokens)) {
        output += escapeHtml(code.slice(offset, match.index));

        const kind = match[1]
            ? "attribute"
            : match[2]
              ? "string"
              : match[3]
                ? "comment"
                : match[4]
                  ? "variable"
                  : match[5]
                    ? "keyword"
                    : match[6]
                      ? "type"
                      : match[7]
                        ? "operator"
                        : "number";

        output += `<span class="syntax-${kind}">${escapeHtml(match[0])}</span>`;
        offset = match.index + match[0].length;
    }

    return output + escapeHtml(code.slice(offset));
}

async function copyInstall() {
    try {
        await navigator.clipboard.writeText(
            "composer require kettasoft/filterable",
        );
        copied.value = true;
        clearTimeout(copyTimer);
        copyTimer = setTimeout(() => (copied.value = false), 1800);
    } catch {
        copied.value = false;
    }
}

function moveGlow(event) {
    if (!root.value) return;
    const bounds = root.value.getBoundingClientRect();
    root.value.style.setProperty(
        "--pointer-x",
        `${event.clientX - bounds.left}px`,
    );
    root.value.style.setProperty(
        "--pointer-y",
        `${event.clientY - bounds.top}px`,
    );
}

function updateScrollCue() {
    hasScrolled.value = window.scrollY > 36;
}

onMounted(() => {
    updateScrollCue();
    window.addEventListener("scroll", updateScrollCue, { passive: true });

    if (
        !root.value ||
        window.matchMedia("(prefers-reduced-motion: reduce)").matches
    )
        return;
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 },
    );
    root.value
        .querySelectorAll(".reveal")
        .forEach((element) => observer.observe(element));
});

onUnmounted(() => {
    observer?.disconnect();
    clearTimeout(copyTimer);
    window.removeEventListener("scroll", updateScrollCue);
});
</script>

<template>
    <main
        ref="root"
        class="filterable-home"
        :class="{ 'has-scrolled': hasScrolled }"
        @pointermove.passive="moveGlow"
    >
        <div class="ambient ambient-one" aria-hidden="true"></div>
        <div class="ambient ambient-two" aria-hidden="true"></div>

        <section class="hero-section">
            <div class="hero-grid" aria-hidden="true"></div>
            <div class="hero-copy">
                <a
                    class="release-pill"
                    href="https://github.com/kettasoft/filterable"
                    target="_blank"
                    rel="noreferrer"
                >
                    <span class="pulse-dot"></span> Laravel 10 · 11 · 12
                    <span class="pill-arrow">↗</span>
                </a>
                <h1>
                    <span class="headline-base">Eloquent filters,</span
                    ><span class="headline-accent"
                        >without the controller noise.</span
                    >
                </h1>
                <p class="hero-lead">
                    Turn request data into expressive, secure queries through
                    four purpose-built engines—without scattering conditional
                    logic across your application.
                </p>
                <div class="hero-actions">
                    <a class="button button-primary" href="./quick-start.html"
                        >Start building <span>→</span></a
                    >
                    <a
                        class="button button-ghost"
                        href="https://github.com/kettasoft/filterable"
                        target="_blank"
                        rel="noreferrer"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                fill="currentColor"
                                d="M12 .7a11.5 11.5 0 0 0-3.64 22.4c.58.1.79-.25.79-.56v-2.23c-3.22.7-3.9-1.37-3.9-1.37-.52-1.34-1.29-1.7-1.29-1.7-1.05-.72.08-.7.08-.7 1.17.08 1.78 1.2 1.78 1.2 1.04 1.77 2.72 1.26 3.38.96.1-.75.4-1.26.74-1.55-2.57-.3-5.27-1.29-5.27-5.69 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.16 1.18a10.98 10.98 0 0 1 5.76 0c2.2-1.49 3.16-1.18 3.16-1.18.63 1.59.23 2.76.11 3.05.74.81 1.19 1.83 1.19 3.09 0 4.42-2.71 5.39-5.29 5.68.42.36.78 1.06.78 2.14v3.17c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .7Z"
                            />
                        </svg>
                        View on GitHub
                    </a>
                </div>
                <button
                    class="install-command"
                    type="button"
                    :aria-label="copied ? 'Copied' : 'Copy install command'"
                    @click="copyInstall"
                >
                    <span class="terminal-mark">$</span
                    ><code>composer require kettasoft/filterable</code
                    ><span class="copy-state" :class="{ copied: copied }">
                        {{ copied ? "Copied!" : "Copy" }}
                    </span>
                </button>
                <div class="hero-proof" aria-label="Package highlights">
                    <span><strong>4</strong> filter engines</span
                    ><span><strong>1</strong> fluent API</span
                    ><span><strong>0</strong> controller clutter</span>
                </div>
            </div>

            <div
                class="hero-visual"
                aria-label="Filterable request pipeline preview"
            >
                <div class="orbit orbit-one" aria-hidden="true"></div>
                <div class="orbit orbit-two" aria-hidden="true"></div>
                <div class="code-window">
                    <div class="window-bar">
                        <div class="window-dots" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </div>
                        <span>PostController.php</span
                        ><span class="window-status">ready</span>
                    </div>
                    <div class="code-body" aria-hidden="true">
                        <div>
                            <span class="code-muted">01</span
                            ><span class="code-pink">$posts</span>
                            <span class="code-blue">=</span> Post<span
                                class="code-blue"
                                >::</span
                            ><span class="code-yellow">filter</span>()
                        </div>
                        <div>
                            <span class="code-muted">02</span
                            >&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-blue"
                                >-&gt;</span
                            ><span class="code-yellow">with</span>(<span
                                class="code-green"
                                >'author'</span
                            >)
                        </div>
                        <div>
                            <span class="code-muted">03</span
                            >&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-blue"
                                >-&gt;</span
                            ><span class="code-yellow">latest</span>()
                        </div>
                        <div>
                            <span class="code-muted">04</span
                            >&nbsp;&nbsp;&nbsp;&nbsp;<span class="code-blue"
                                >-&gt;</span
                            ><span class="code-yellow">paginate</span>();
                        </div>
                    </div>
                    <div class="pipeline">
                        <div class="pipeline-label">Live pipeline</div>
                        <div class="pipeline-row">
                            <span class="pipeline-node"><i>01</i> Request</span
                            ><span class="pipeline-line"><i></i></span
                            ><span class="pipeline-node"><i>02</i> Payload</span
                            ><span class="pipeline-line"><i></i></span
                            ><span class="pipeline-node active"
                                ><i>03</i> Query</span
                            >
                        </div>
                    </div>
                </div>
                <div class="floating-chip chip-validation">
                    <span>✓</span> Validated
                </div>
                <div class="floating-chip chip-engine">
                    <span>⚡</span> Invokable
                </div>
                <div class="result-card">
                    <span class="result-icon">↳</span>
                    <div>
                        <small>Generated query</small
                        ><strong>8 matching posts</strong>
                    </div>
                    <span class="result-time">12ms</span>
                </div>
            </div>

            <a
                class="scroll-cue"
                href="#engines"
                aria-label="Scroll to explore Filterable engines"
            >
                <span>Scroll to explore</span>
                <i aria-hidden="true">
                    <svg viewBox="0 0 20 20">
                        <path d="m5.5 7.5 4.5 4 4.5-4" />
                    </svg>
                </i>
            </a>
        </section>

        <div class="capability-strip" aria-label="Core capabilities">
            <div class="strip-track">
                <span>Payload-first</span><i></i><span>Relation-aware</span
                ><i></i><span>Validated</span><i></i><span>Sanitized</span
                ><i></i><span>Cache-ready</span><i></i><span>Observable</span
                ><i></i> <span>Payload-first</span><i></i
                ><span>Relation-aware</span><i></i><span>Validated</span><i></i
                ><span>Sanitized</span><i></i><span>Cache-ready</span><i></i
                ><span>Observable</span><i></i>
            </div>
        </div>

        <section id="engines" class="home-section engines-section reveal">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">One package, four approaches</span>
                    <h2>
                        Choose the request contract.<br /><em
                            >Keep the same fluent query.</em
                        >
                    </h2>
                </div>
                <p>
                    Each engine solves a different API shape. Start simple, go
                    deeply relational, or accept complex boolean trees without
                    rewriting your application layer.
                </p>
            </div>
            <div class="engine-grid">
                <a
                    v-for="engine in engines"
                    :key="engine.id"
                    class="engine-card"
                    :class="`engine-${engine.id}`"
                    :href="engine.href"
                >
                    <div class="engine-top">
                        <span>{{ engine.index }}</span
                        ><span class="engine-label">{{ engine.label }}</span>
                    </div>
                    <div class="engine-symbol" aria-hidden="true">
                        <span v-if="engine.id === 'invokable'">ƒ</span
                        ><span v-else-if="engine.id === 'ruleset'">≡</span
                        ><span v-else-if="engine.id === 'expression'">↳</span
                        ><span v-else>⌘</span>
                    </div>
                    <h3>{{ engine.name }}</h3>
                    <p>{{ engine.description }}</p>
                    <code>{{ engine.request }}</code
                    ><span class="card-link">Explore engine <b>→</b></span>
                </a>
            </div>
        </section>

        <section id="workflow" class="home-section workflow-section reveal">
            <div class="workflow-intro">
                <span class="eyebrow">A predictable runtime</span>
                <h2>From untrusted input<br />to a clean query.</h2>
                <p>
                    Every condition follows one visible lifecycle, so behavior
                    stays testable as filtering grows.
                </p>
                <a href="./how-it-works.html"
                    >See how the runtime works <span>→</span></a
                >
            </div>
            <div class="workflow-list">
                <article>
                    <span class="step-number">01</span>
                    <div>
                        <h3>Authorize</h3>
                        <p>
                            Decide whether the current user may apply a filter
                            before it touches the query.
                        </p>
                    </div>
                    <span class="step-icon">⌁</span>
                </article>
                <article>
                    <span class="step-number">02</span>
                    <div>
                        <h3>Validate</h3>
                        <p>
                            Use familiar Laravel rules to reject malformed
                            request data with useful errors.
                        </p>
                    </div>
                    <span class="step-icon">✓</span>
                </article>
                <article>
                    <span class="step-number">03</span>
                    <div>
                        <h3>Transform</h3>
                        <p>
                            Trim, cast, map, sanitize, or enrich values through
                            a consistent Payload.
                        </p>
                    </div>
                    <span class="step-icon">◇</span>
                </article>
                <article>
                    <span class="step-number">04</span>
                    <div>
                        <h3>Apply</h3>
                        <p>
                            Generate direct or relational Eloquent constraints
                            and record the outcome.
                        </p>
                    </div>
                    <span class="step-icon">→</span>
                </article>
            </div>
        </section>

        <section id="examples" class="home-section code-section reveal">
            <div class="code-showcase">
                <div
                    class="code-tabs"
                    role="tablist"
                    aria-label="Engine examples"
                >
                    <button
                        v-for="engine in engines"
                        :key="engine.id"
                        type="button"
                        role="tab"
                        :aria-selected="activeEngine === engine.id"
                        :class="{ active: activeEngine === engine.id }"
                        @click="activeEngine = engine.id"
                    >
                        {{ engine.name }}
                    </button>
                </div>
                <div class="showcase-window">
                    <div class="showcase-file">
                        <span>{{
                            activeEngine === "tree"
                                ? "request.json"
                                : "PostFilter.php"
                        }}</span
                        ><span>PHP 8.2+</span>
                    </div>
                    <pre><code v-html="highlightedCode"></code></pre>
                </div>
            </div>
            <div class="code-copy">
                <span class="eyebrow">Complex when you need it</span>
                <h2>Your API shape.<br /><em>Your engine.</em></h2>
                <p>
                    Switch approaches without losing the package features around
                    them. All engines share the same builder integration,
                    operator pipeline, runtime context, and observability.
                </p>
                <ul>
                    <li><span>✓</span> Automatic builder forwarding</li>
                    <li>
                        <span>✓</span> Explicit field and operator allowlists
                    </li>
                    <li><span>✓</span> Direct and deeply nested relations</li>
                    <li><span>✓</span> Strict or permissive error handling</li>
                </ul>
            </div>
        </section>

        <section id="features" class="home-section bento-section reveal">
            <div class="section-heading compact">
                <div>
                    <span class="eyebrow">More than query conditions</span>
                    <h2>Built for real APIs.</h2>
                </div>
                <p>
                    The surrounding tools are part of the package—not an
                    afterthought you have to rebuild in every project.
                </p>
            </div>
            <div class="bento-grid">
                <a class="bento-card bento-large" href="./api/payload.html"
                    ><span class="bento-kicker">Payload API</span>
                    <h3>One value.<br />Its full story.</h3>
                    <p>
                        Keep the field, resolved operator, transformed value,
                        and raw input together across the entire lifecycle.
                    </p>
                    <div class="payload-orbit" aria-hidden="true">
                        <span class="orbit-item"><i>field</i></span>
                        <span class="orbit-item"><i>operator</i></span>
                        <span class="orbit-item"><i>value</i></span>
                        <b>Payload</b>
                    </div></a
                >
                <a class="bento-card bento-security" href="./authorization.html"
                    ><span class="bento-icon">⌾</span
                    ><span class="bento-kicker">Guardrails</span>
                    <h3>Secure by design</h3>
                    <p>
                        Authorization, validation, sanitization, and explicit
                        allowlists work before query application.
                    </p>
                    <div class="security-bars" aria-hidden="true">
                        <i></i><i></i><i></i></div
                ></a>
                <a class="bento-card" href="./caching/overview.html"
                    ><span class="bento-icon">↯</span
                    ><span class="bento-kicker">Caching</span>
                    <h3>Fast on repeat</h3>
                    <p>
                        Multiple strategies, scoped keys, tags, profiles, and
                        automatic invalidation.
                    </p></a
                >
                <a class="bento-card" href="./events/"
                    ><span class="bento-icon">◉</span
                    ><span class="bento-kicker">Events & profiler</span>
                    <h3>See what happened</h3>
                    <p>
                        Inspect applied and skipped payloads, subscribe to
                        lifecycle events, and profile execution.
                    </p></a
                >
                <a class="bento-card bento-wide" href="./cli/setup.html"
                    ><div>
                        <span class="bento-kicker">Developer tooling</span>
                        <h3>A CLI that shortens the loop.</h3>
                        <p>
                            Set up, generate, discover, inspect, list, and test
                            filters without leaving the terminal.
                        </p>
                    </div>
                    <div class="mini-terminal" aria-hidden="true">
                        <span
                            ><i>$</i> php artisan filterable:inspect
                            PostFilter</span
                        ><span class="terminal-response"
                            >✓ 2 filters · invokable · ready</span
                        >
                    </div></a
                >
            </div>
        </section>

        <section id="ai-guide" class="home-section ai-section reveal">
            <div class="ai-glow" aria-hidden="true"></div>
            <div class="ai-badge">AI-ready docs</div>
            <h2>Give your coding assistant<br /><em>the right context.</em></h2>
            <p>
                Download the official package instructions so AI tools use the
                current APIs, choose the right engine, and avoid legacy
                patterns.
            </p>
            <div class="ai-actions">
                <a class="button button-primary" href="./ai-assistant.html"
                    >Set up the AI guide <span>→</span></a
                ><a class="text-link" href="./filterable-ai.md"
                    >Download the instruction file ↓</a
                >
            </div>
        </section>

        <section class="final-cta reveal">
            <div>
                <span class="eyebrow">Ready when your API is</span>
                <h2>Build filters that stay<br />pleasant to maintain.</h2>
            </div>
            <div class="final-actions">
                <a class="button button-primary" href="./installation.html"
                    >Install Filterable <span>→</span></a
                ><a href="./engines/invokable/">Browse the engines</a>
            </div>
        </section>
    </main>
</template>

<style scoped>
.filterable-home {
    --home-red: #ff3d2e;
    --home-orange: #ff7548;
    --home-ink: #11131a;
    --home-muted: #606573;
    --home-line: rgba(17, 19, 26, 0.1);
    --home-panel: #f7f7f9;
    --pointer-x: 70%;
    --pointer-y: 10%;
    position: relative;
    overflow: hidden;
    color: var(--home-ink);
    background: #fff;
}
.filterable-home * {
    box-sizing: border-box;
}
[data-theme="dark"] .filterable-home {
    --home-ink: #f5f5f7;
    --home-muted: #a7a9b4;
    --home-line: rgba(255, 255, 255, 0.1);
    --home-panel: #171820;
    background: #0d0e13;
}
.filterable-home:before {
    position: absolute;
    z-index: 0;
    width: 520px;
    height: 520px;
    left: calc(var(--pointer-x) - 260px);
    top: calc(var(--pointer-y) - 260px);
    border-radius: 50%;
    background: radial-gradient(
        circle,
        rgba(255, 61, 46, 0.09),
        transparent 68%
    );
    content: "";
    pointer-events: none;
    transition:
        left 0.35s ease-out,
        top 0.35s ease-out;
}
.hero-section,
.home-section,
.final-cta {
    position: relative;
    z-index: 1;
    width: min(1180px, calc(100% - 48px));
    margin-inline: auto;
}
.hero-section {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(390px, 0.85fr);
    align-items: center;
    gap: 52px;
    min-height: calc(100vh - var(--vp-nav-height));
    padding: 68px 0 55px;
}
.hero-copy,
.hero-visual,
.section-heading > *,
.code-showcase,
.code-copy {
    min-width: 0;
}
.hero-grid {
    position: absolute;
    inset: 0 -18vw;
    z-index: -2;
    opacity: 0.45;
    background-image:
        linear-gradient(var(--home-line) 1px, transparent 1px),
        linear-gradient(90deg, var(--home-line) 1px, transparent 1px);
    background-size: 72px 72px;
    mask-image: linear-gradient(
        to bottom,
        transparent,
        #000 18%,
        #000 65%,
        transparent 96%
    );
}
.ambient {
    position: absolute;
    border-radius: 50%;
    filter: blur(4px);
    pointer-events: none;
}
.ambient-one {
    width: 480px;
    height: 480px;
    top: 50px;
    right: -260px;
    background: rgba(255, 61, 46, 0.09);
}
.ambient-two {
    width: 360px;
    height: 360px;
    top: 900px;
    left: -250px;
    background: rgba(103, 89, 255, 0.08);
}
.release-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding: 8px 12px;
    border: 1px solid var(--home-line);
    border-radius: 999px;
    color: var(--home-muted);
    background: color-mix(in srgb, var(--home-panel) 74%, transparent);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    text-decoration: none;
}
.pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #25b46b;
    box-shadow: 0 0 0 5px rgba(37, 180, 107, 0.12);
    animation: pulse 2.2s infinite;
}
.pill-arrow {
    color: var(--home-red);
}
.hero-copy h1 {
    max-width: 720px;
    margin: 0;
    font-size: clamp(3.4rem, 5.2vw, 5.25rem);
    line-height: 0.92;
    letter-spacing: -0.072em;
    font-weight: 780;
}
.hero-copy h1 span {
    display: block;
}
.hero-copy h1 .headline-base {
    color: var(--home-ink);
}
.hero-copy h1 .headline-accent {
    color: transparent;
    background: linear-gradient(
        100deg,
        var(--home-red),
        var(--home-orange) 58%,
        #ffad65
    );
    background-clip: text;
    -webkit-background-clip: text;
}
.hero-lead {
    max-width: 630px;
    margin: 22px 0 0;
    color: var(--home-muted);
    font-size: clamp(1rem, 1.35vw, 1.15rem);
    line-height: 1.65;
}
.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 24px;
}
.button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-height: 50px;
    padding: 0 21px;
    border: 1px solid transparent;
    border-radius: 13px;
    font-size: 14px;
    font-weight: 750;
    text-decoration: none;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}
.button:hover {
    transform: translateY(-2px);
    text-decoration: none;
}
.button-primary {
    color: #fff;
    background: linear-gradient(135deg, var(--home-red), #f52a1c);
    box-shadow: 0 13px 30px rgba(255, 61, 46, 0.25);
}
.button-primary:hover {
    color: #fff;
    box-shadow: 0 17px 38px rgba(255, 61, 46, 0.34);
}
.button-ghost {
    color: var(--home-ink);
    border-color: var(--home-line);
    background: color-mix(in srgb, var(--home-panel) 65%, transparent);
}
.button-ghost:hover {
    color: var(--home-red);
    border-color: rgba(255, 61, 46, 0.34);
}
.button-ghost svg {
    width: 18px;
    height: 18px;
}
.install-command {
    display: flex;
    align-items: center;
    width: min(100%, 455px);
    margin-top: 12px;
    padding: 12px 14px;
    border: 1px solid var(--home-line);
    border-radius: 12px;
    color: var(--home-ink);
    background: color-mix(in srgb, var(--home-panel) 70%, transparent);
    cursor: pointer;
    text-align: left;
}
.terminal-mark {
    margin-right: 10px;
    color: var(--home-red);
    font: 700 14px/1 monospace;
}
.install-command code {
    overflow: hidden;
    flex: 1;
    color: inherit;
    background: transparent;
    font-size: 12.5px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.copy-state {
    margin-left: 12px;
    color: var(--home-muted);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}
.copy-state.copied {
    color: #1c9e5d;
}
.hero-proof {
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
    margin-top: 17px;
    color: var(--home-muted);
    font-size: 12px;
}
.hero-proof span {
    display: flex;
    align-items: baseline;
    gap: 6px;
}
.hero-proof strong {
    color: var(--home-ink);
    font-size: 17px;
}
.hero-visual {
    position: relative;
    min-height: 460px;
    perspective: 1000px;
}
.code-window {
    position: absolute;
    inset: 30px 8px 38px 15px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 22px;
    background: #14151c;
    box-shadow:
        0 35px 90px rgba(18, 19, 25, 0.26),
        0 0 0 8px rgba(255, 255, 255, 0.02);
    transform: rotateY(-5deg) rotateX(2deg);
    animation: window-float 6s ease-in-out infinite;
}
.window-bar {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    height: 50px;
    padding: 0 17px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    color: #8b8e9c;
    font-size: 11px;
}
.window-dots {
    display: flex;
    gap: 6px;
}
.window-dots span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #3a3c47;
}
.window-dots span:first-child {
    background: #ff5e52;
}
.window-dots span:nth-child(2) {
    background: #f9bd4f;
}
.window-dots span:last-child {
    background: #37c65b;
}
.window-status {
    justify-self: end;
    color: #58cb83;
}
.window-status:before {
    display: inline-block;
    width: 5px;
    height: 5px;
    margin-right: 6px;
    border-radius: 50%;
    background: currentColor;
    content: "";
}
.code-body {
    padding: 30px 25px 22px;
    color: #e4e4e8;
    font:
        500 clamp(12px, 1.2vw, 14px)/1.9 ui-monospace,
        SFMono-Regular,
        Menlo,
        monospace;
}
.code-body div {
    white-space: nowrap;
}
.code-muted {
    display: inline-block;
    width: 36px;
    color: #50525e;
    user-select: none;
}
.code-pink {
    color: #ff7b91;
}
.code-blue {
    color: #7ca7ff;
}
.code-yellow {
    color: #e6c879;
}
.code-green {
    color: #9bd778;
}
.pipeline {
    margin: 2px 18px 0;
    padding: 16px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.025);
}
.pipeline-label {
    margin-bottom: 14px;
    color: #777a87;
    font-size: 10px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}
.pipeline-row {
    display: flex;
    align-items: center;
}
.pipeline-node {
    display: flex;
    flex-direction: column;
    gap: 5px;
    color: #c9cad0;
    font-size: 11px;
}
.pipeline-node i {
    color: #686b76;
    font-size: 8px;
    font-style: normal;
}
.pipeline-node.active {
    color: #fff;
}
.pipeline-node.active i {
    color: #ff756a;
}
.pipeline-line {
    position: relative;
    flex: 1;
    height: 1px;
    margin: 0 10px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.12);
}
.pipeline-line i {
    position: absolute;
    width: 42%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        var(--home-red),
        transparent
    );
    animation: flow 2.4s linear infinite;
}
.floating-chip {
    position: absolute;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 13px;
    border: 1px solid var(--home-line);
    border-radius: 11px;
    color: var(--home-ink);
    background: color-mix(in srgb, var(--vp-c-bg) 90%, transparent);
    box-shadow: 0 16px 40px rgba(18, 19, 25, 0.12);
    backdrop-filter: blur(12px);
    font-size: 11px;
    font-weight: 750;
    animation: chip-float 5s ease-in-out infinite;
}
.floating-chip span {
    color: var(--home-red);
}
.chip-validation {
    top: 5px;
    right: -8px;
}
.chip-engine {
    left: -10px;
    bottom: 58px;
    animation-delay: -2.5s;
}
.result-card {
    position: absolute;
    right: -12px;
    bottom: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 225px;
    padding: 13px 14px;
    border: 1px solid var(--home-line);
    border-radius: 14px;
    color: var(--home-ink);
    background: color-mix(in srgb, var(--vp-c-bg) 94%, transparent);
    box-shadow: 0 20px 45px rgba(18, 19, 25, 0.14);
    backdrop-filter: blur(14px);
}
.result-icon {
    display: grid;
    width: 30px;
    height: 30px;
    place-items: center;
    border-radius: 9px;
    color: #fff;
    background: var(--home-red);
}
.result-card div {
    display: flex;
    flex: 1;
    flex-direction: column;
}
.result-card small {
    color: var(--home-muted);
    font-size: 9px;
}
.result-card strong {
    font-size: 11px;
}
.result-time {
    color: #34ae68;
    font: 700 10px monospace;
}
.orbit {
    position: absolute;
    border: 1px dashed rgba(255, 61, 46, 0.18);
    border-radius: 50%;
    animation: rotate 22s linear infinite;
}
.orbit-one {
    width: 350px;
    height: 350px;
    top: 45px;
    right: -110px;
}
.orbit-two {
    width: 210px;
    height: 210px;
    left: -60px;
    bottom: 24px;
    animation-direction: reverse;
}
.scroll-cue {
    position: absolute;
    z-index: 4;
    left: 50%;
    bottom: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--home-muted);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-decoration: none;
    text-transform: uppercase;
    transform: translateX(-50%);
    transition:
        opacity 0.45s ease,
        filter 0.45s ease,
        transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}
.scroll-cue:hover {
    color: var(--home-ink);
    text-decoration: none;
}
.scroll-cue i {
    display: grid;
    width: 29px;
    height: 40px;
    place-items: center;
    border: 1px solid var(--home-line);
    border-radius: 999px;
    background: color-mix(in srgb, var(--home-panel) 62%, transparent);
    box-shadow: 0 8px 24px rgba(18, 19, 25, 0.08);
    backdrop-filter: blur(10px);
}
.scroll-cue svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: var(--home-red);
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.6;
    animation: scroll-nudge 1.9s ease-in-out infinite;
}
.has-scrolled .scroll-cue {
    opacity: 0;
    filter: blur(4px);
    pointer-events: none;
    transform: translate(-50%, 12px);
}
.capability-strip {
    position: relative;
    z-index: 1;
    overflow: hidden;
    border-block: 1px solid var(--home-line);
    background: color-mix(in srgb, var(--home-panel) 55%, transparent);
}
.strip-track {
    display: flex;
    align-items: center;
    gap: 28px;
    width: max-content;
    padding: 15px 0;
    color: var(--home-muted);
    font-size: 11px;
    font-weight: 750;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    animation: marquee 28s linear infinite;
}
.strip-track i {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--home-red);
}
.home-section {
    padding: 120px 0;
}
.section-heading {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    align-items: end;
    gap: 80px;
    margin-bottom: 55px;
}
.section-heading.compact {
    align-items: center;
}
.eyebrow {
    display: block;
    margin-bottom: 17px;
    color: var(--home-red);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.15em;
    text-transform: uppercase;
}
.section-heading h2,
.workflow-intro h2,
.code-copy h2,
.ai-section h2,
.final-cta h2 {
    margin: 0;
    color: var(--home-ink);
    font-size: clamp(2.3rem, 4.5vw, 4.4rem);
    line-height: 1.02;
    letter-spacing: -0.055em;
}
.section-heading h2 em,
.code-copy h2 em,
.ai-section h2 em {
    color: var(--home-red);
    font-style: normal;
}
.section-heading p,
.workflow-intro p,
.code-copy > p {
    margin: 0;
    color: var(--home-muted);
    line-height: 1.75;
}
.engine-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.engine-card {
    position: relative;
    display: flex;
    min-height: 380px;
    flex-direction: column;
    overflow: hidden;
    padding: 24px;
    border: 1px solid var(--home-line);
    border-radius: 20px;
    color: var(--home-ink);
    background: var(--home-panel);
    text-decoration: none;
    transition:
        transform 0.3s ease,
        border-color 0.3s ease,
        box-shadow 0.3s ease;
}
.engine-card:after {
    position: absolute;
    width: 170px;
    height: 170px;
    right: -70px;
    top: -70px;
    border-radius: 50%;
    background: var(--engine-color, var(--home-red));
    opacity: 0.08;
    content: "";
    transition:
        transform 0.4s ease,
        opacity 0.4s ease;
}
.engine-card:hover {
    z-index: 2;
    color: var(--home-ink);
    border-color: color-mix(in srgb, var(--engine-color) 42%, transparent);
    box-shadow: 0 25px 55px rgba(18, 19, 25, 0.11);
    transform: translateY(-9px);
    text-decoration: none;
}
.engine-card:hover:after {
    opacity: 0.16;
    transform: scale(1.3);
}
.engine-ruleset {
    --engine-color: #6b7cff;
}
.engine-expression {
    --engine-color: #8b5cf6;
}
.engine-tree {
    --engine-color: #1ba875;
}
.engine-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: var(--home-muted);
    font: 700 10px/1 monospace;
}
.engine-label {
    padding: 6px 8px;
    border: 1px solid var(--home-line);
    border-radius: 99px;
}
.engine-symbol {
    display: grid;
    width: 55px;
    height: 55px;
    margin-top: 44px;
    place-items: center;
    border-radius: 16px;
    color: var(--engine-color, var(--home-red));
    background: color-mix(
        in srgb,
        var(--engine-color, var(--home-red)) 10%,
        transparent
    );
    font-size: 25px;
    font-weight: 750;
}
.engine-card h3 {
    margin: 21px 0 9px;
    font-size: 24px;
    letter-spacing: -0.035em;
}
.engine-card p {
    margin: 0;
    color: var(--home-muted);
    font-size: 13px;
    line-height: 1.65;
}
.engine-card code {
    overflow: hidden;
    margin-top: auto;
    padding-top: 25px;
    color: color-mix(
        in srgb,
        var(--engine-color, var(--home-red)) 80%,
        var(--home-ink)
    );
    background: none;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.card-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
    padding-top: 15px;
    border-top: 1px solid var(--home-line);
    color: var(--home-muted);
    font-size: 11px;
    font-weight: 700;
}
.card-link b {
    color: var(--engine-color, var(--home-red));
    font-size: 16px;
}
.workflow-section {
    display: grid;
    grid-template-columns: 0.72fr 1.28fr;
    gap: 100px;
}
.workflow-intro {
    position: sticky;
    top: 110px;
    align-self: start;
}
.workflow-intro h2 {
    margin-bottom: 25px;
}
.workflow-intro p {
    margin-bottom: 28px;
}
.workflow-intro a {
    color: var(--home-ink);
    font-size: 13px;
    font-weight: 750;
    text-decoration: none;
}
.workflow-intro a span {
    margin-left: 6px;
    color: var(--home-red);
}
.workflow-list {
    border-top: 1px solid var(--home-line);
}
.workflow-list article {
    display: grid;
    grid-template-columns: 44px 1fr 50px;
    gap: 18px;
    align-items: start;
    padding: 32px 8px;
    border-bottom: 1px solid var(--home-line);
    transition:
        padding 0.25s ease,
        background 0.25s ease;
}
.workflow-list article:hover {
    padding-inline: 18px;
    background: var(--home-panel);
}
.step-number {
    padding-top: 5px;
    color: var(--home-red);
    font: 700 10px monospace;
}
.workflow-list h3 {
    margin: 0 0 8px;
    font-size: 23px;
}
.workflow-list p {
    max-width: 520px;
    margin: 0;
    color: var(--home-muted);
    font-size: 13px;
    line-height: 1.65;
}
.step-icon {
    display: grid;
    width: 43px;
    height: 43px;
    place-items: center;
    border: 1px solid var(--home-line);
    border-radius: 50%;
    color: var(--home-red);
}
.code-section {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(300px, 0.85fr);
    align-items: center;
    gap: 85px;
}
.code-showcase {
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 22px;
    background: #13141a;
    box-shadow: 0 30px 80px rgba(15, 16, 22, 0.2);
}
.code-tabs {
    display: flex;
    overflow-x: auto;
    gap: 4px;
    padding: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.code-tabs button {
    padding: 9px 12px;
    border: 0;
    border-radius: 8px;
    color: #777a86;
    background: transparent;
    cursor: pointer;
    font-size: 10px;
    font-weight: 700;
}
.code-tabs button.active {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
}
.showcase-file {
    display: flex;
    justify-content: space-between;
    padding: 13px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    color: #656874;
    font: 600 9px monospace;
}
.showcase-window pre {
    height: 395px;
    margin: 0;
    padding: 30px;
    overflow: auto;
    background: transparent;
}
.showcase-window code {
    color: #c9cad2;
    background: transparent;
    font:
        500 12px/1.85 ui-monospace,
        SFMono-Regular,
        Menlo,
        monospace;
}
.showcase-window :deep(.syntax-keyword),
.showcase-window :deep(.syntax-attribute) {
    color: #ff7b91;
}
.showcase-window :deep(.syntax-type) {
    color: #82aaff;
}
.showcase-window :deep(.syntax-variable) {
    color: #ff927d;
}
.showcase-window :deep(.syntax-string) {
    color: #a8dc76;
}
.showcase-window :deep(.syntax-operator) {
    color: #78b7ff;
}
.showcase-window :deep(.syntax-number) {
    color: #c69bf4;
}
.showcase-window :deep(.syntax-comment) {
    color: #676b78;
    font-style: italic;
}
.code-copy h2 {
    margin-bottom: 25px;
}
.code-copy ul {
    display: grid;
    gap: 13px;
    margin: 30px 0 0;
    padding: 0;
    list-style: none;
}
.code-copy li {
    color: var(--home-muted);
    font-size: 13px;
}
.code-copy li span {
    display: inline-grid;
    width: 20px;
    height: 20px;
    margin-right: 9px;
    place-items: center;
    border-radius: 50%;
    color: #1c9e5d;
    background: rgba(28, 158, 93, 0.1);
    font-size: 9px;
}
.bento-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
.bento-card {
    position: relative;
    min-height: 260px;
    overflow: hidden;
    padding: 27px;
    border: 1px solid var(--home-line);
    border-radius: 20px;
    color: var(--home-ink);
    background: var(--home-panel);
    text-decoration: none;
    transition:
        transform 0.25s ease,
        border-color 0.25s ease;
}
.bento-card:hover {
    color: var(--home-ink);
    border-color: rgba(255, 61, 46, 0.35);
    transform: translateY(-5px);
    text-decoration: none;
}
.bento-large {
    grid-row: span 2;
    min-height: 534px;
    background: #16171e;
    color: #fff;
}
.bento-large:hover {
    color: #fff;
}
.bento-wide {
    display: grid;
    grid-column: span 2;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 30px;
}
.bento-kicker {
    color: var(--home-red);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.13em;
    text-transform: uppercase;
}
.bento-icon {
    display: grid;
    width: 36px;
    height: 36px;
    margin-bottom: 34px;
    place-items: center;
    border-radius: 11px;
    color: var(--home-red);
    background: rgba(255, 61, 46, 0.1);
}
.bento-card h3 {
    margin: 12px 0 10px;
    font-size: 24px;
    line-height: 1.12;
    letter-spacing: -0.035em;
}
.bento-large h3 {
    margin-top: 24px;
    font-size: 42px;
}
.bento-card p {
    margin: 0;
    color: var(--home-muted);
    font-size: 12px;
    line-height: 1.7;
}
.bento-large p {
    color: #989aa6;
}
.payload-orbit {
    position: absolute;
    width: 280px;
    height: 280px;
    left: 50%;
    bottom: -75px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translateX(-50%);
}
.payload-orbit:before {
    position: absolute;
    inset: 40px;
    border: 1px dashed rgba(255, 61, 46, 0.35);
    border-radius: 50%;
    content: "";
}
.payload-orbit b {
    position: absolute;
    inset: 90px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    color: #fff;
    background: var(--home-red);
    font-size: 11px;
}
.payload-orbit .orbit-item {
    position: absolute;
    inset: 0;
    animation: orbit-item 20s linear infinite;
}
.payload-orbit .orbit-item:nth-child(2) {
    animation-delay: -6.666s;
}
.payload-orbit .orbit-item:nth-child(3) {
    animation-delay: -13.333s;
}
.payload-orbit .orbit-item i {
    position: absolute;
    top: 50%;
    right: -12px;
    padding: 5px 9px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 99px;
    color: #bbbcc5;
    background: #1d1e26;
    font: 9px monospace;
    font-style: normal;
    white-space: nowrap;
    animation: orbit-label 20s linear infinite;
}
.payload-orbit .orbit-item:nth-child(2) i {
    animation-delay: -6.666s;
}
.payload-orbit .orbit-item:nth-child(3) i {
    animation-delay: -13.333s;
}
.security-bars {
    position: absolute;
    right: 25px;
    bottom: 25px;
    left: 25px;
    display: flex;
    gap: 5px;
}
.security-bars i {
    height: 5px;
    flex: 1;
    border-radius: 4px;
    background: #27b36c;
    opacity: 0.35;
}
.security-bars i:nth-child(2) {
    opacity: 0.65;
}
.security-bars i:nth-child(3) {
    opacity: 1;
}
.mini-terminal {
    display: flex;
    flex-direction: column;
    gap: 13px;
    padding: 18px;
    border-radius: 13px;
    background: #15161c;
    color: #d9d9df;
    font: 9px/1.5 monospace;
}
.mini-terminal i {
    color: var(--home-red);
    font-style: normal;
}
.terminal-response {
    color: #55c383;
}
.ai-section {
    overflow: hidden;
    margin-block: 40px 120px;
    padding: 85px 8%;
    border-radius: 28px;
    color: #fff;
    background: #15161d;
    text-align: center;
}
.ai-section h2 {
    position: relative;
    color: #fff;
}
.ai-section p {
    position: relative;
    max-width: 630px;
    margin: 25px auto 0;
    color: #a8a9b2;
    line-height: 1.75;
}
.ai-badge {
    position: relative;
    display: inline-block;
    margin-bottom: 25px;
    padding: 7px 10px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 99px;
    color: #ff8a80;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}
.ai-glow {
    position: absolute;
    width: 125%;
    height: 330px;
    top: -245px;
    left: -12.5%;
    border-radius: 42% 58% 64% 36% / 55% 42% 58% 45%;
    background: radial-gradient(
        ellipse at center,
        rgba(255, 61, 46, 0.72) 0,
        rgba(255, 61, 46, 0.26) 42%,
        transparent 72%
    );
    filter: blur(34px);
    opacity: 0.58;
    transform-origin: 50% 100%;
    animation: ai-wave 9s ease-in-out infinite;
}
.ai-glow:after {
    position: absolute;
    inset: 55px 8% -45px;
    display: block;
    border-radius: 58% 42% 38% 62% / 46% 62% 38% 54%;
    background: radial-gradient(
        ellipse at center,
        rgba(255, 117, 72, 0.5),
        transparent 68%
    );
    content: "";
    animation: ai-wave-secondary 7s ease-in-out infinite;
    mix-blend-mode: screen;
}
.ai-actions {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 25px;
    margin-top: 32px;
}
.ai-actions .button-primary {
    box-shadow: 0 13px 34px rgba(255, 61, 46, 0.3);
}
.text-link {
    color: #d4d4d9;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}
.text-link:hover {
    color: #fff;
}
.final-cta {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 50px;
    padding: 20px 0 120px;
}
.final-cta h2 {
    font-size: clamp(2.3rem, 4.2vw, 4rem);
}
.final-actions {
    display: flex;
    align-items: center;
    gap: 22px;
}
.final-actions > a:last-child {
    color: var(--home-muted);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}
.final-actions > a:last-child:hover {
    color: var(--home-red);
}
.reveal {
    opacity: 0;
    transform: translateY(28px);
    transition:
        opacity 0.7s ease,
        transform 0.7s ease;
}
.reveal.is-visible {
    opacity: 1;
    transform: none;
}
@keyframes pulse {
    50% {
        box-shadow: 0 0 0 9px rgba(37, 180, 107, 0);
    }
}
@keyframes window-float {
    50% {
        transform: rotateY(-3deg) rotateX(1deg) translateY(-9px);
    }
}
@keyframes chip-float {
    50% {
        transform: translateY(-7px);
    }
}
@keyframes scroll-nudge {
    0%,
    100% {
        transform: translateY(-2px);
        opacity: 0.45;
    }
    50% {
        transform: translateY(3px);
        opacity: 1;
    }
}
@keyframes flow {
    from {
        left: -45%;
    }
    to {
        left: 105%;
    }
}
@keyframes rotate {
    to {
        transform: rotate(360deg);
    }
}
@keyframes orbit-item {
    to {
        transform: rotate(360deg);
    }
}
@keyframes orbit-label {
    from {
        transform: translateY(-50%) rotate(0);
    }
    to {
        transform: translateY(-50%) rotate(-360deg);
    }
}
@keyframes ai-wave {
    0%,
    100% {
        border-radius: 42% 58% 64% 36% / 55% 42% 58% 45%;
        transform: translate3d(-3%, 0, 0) rotate(-2deg) scaleX(0.96);
    }
    50% {
        border-radius: 61% 39% 42% 58% / 38% 59% 41% 62%;
        transform: translate3d(4%, 22px, 0) rotate(2deg) scaleX(1.04);
    }
}
@keyframes ai-wave-secondary {
    0%,
    100% {
        transform: translate3d(3%, 4px, 0) rotate(2deg) scale(0.94);
    }
    50% {
        transform: translate3d(-5%, 18px, 0) rotate(-3deg) scale(1.08);
    }
}
@keyframes marquee {
    to {
        transform: translateX(-50%);
    }
}
@media (max-width: 960px) {
    .hero-section {
        grid-template-columns: 1fr;
        gap: 30px;
        padding-top: 80px;
    }
    .hero-visual {
        width: min(100%, 620px);
        min-height: 480px;
        margin-inline: auto;
    }
    .engine-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .workflow-section,
    .code-section {
        grid-template-columns: 1fr;
        gap: 55px;
    }
    .workflow-intro {
        position: static;
    }
    .code-copy {
        order: -1;
    }
    .section-heading {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    .bento-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .bento-large {
        grid-row: auto;
        min-height: 430px;
    }
    .bento-security {
        min-height: 430px;
    }
    .final-cta {
        align-items: flex-start;
        flex-direction: column;
    }
}
@media (max-width: 640px) {
    .hero-section,
    .home-section,
    .final-cta {
        width: min(100% - 30px, 1180px);
    }
    .hero-section {
        min-height: auto;
        padding: 70px 0 86px;
    }
    .hero-copy h1 {
        font-size: clamp(2.65rem, 13vw, 3.5rem);
        overflow-wrap: anywhere;
    }
    .hero-lead {
        font-size: 1rem;
    }
    .hero-actions,
    .hero-actions .button {
        width: 100%;
    }
    .hero-actions .button {
        flex: 1 1 100%;
    }
    .install-command code {
        min-width: 0;
    }
    .hero-proof {
        gap: 13px;
        justify-content: space-between;
    }
    .hero-proof span {
        flex-direction: column;
        gap: 2px;
    }
    .hero-visual {
        min-height: 390px;
    }
    .code-window {
        inset: 35px 0 40px;
        transform: none;
    }
    .code-body {
        padding: 24px 18px;
        font-size: 10px;
    }
    .pipeline {
        margin-inline: 12px;
        padding: 14px;
    }
    .pipeline-node {
        font-size: 9px;
    }
    .chip-validation {
        right: -7px;
    }
    .chip-engine {
        left: -7px;
        bottom: 55px;
    }
    .result-card {
        right: -5px;
        min-width: 195px;
    }
    .scroll-cue span {
        display: none;
    }
    .scroll-cue {
        bottom: 20px;
    }
    .home-section {
        padding: 85px 0;
    }
    .engine-grid,
    .bento-grid {
        grid-template-columns: 1fr;
    }
    .engine-card {
        min-height: 340px;
    }
    .bento-wide {
        grid-column: auto;
        grid-template-columns: 1fr;
    }
    .bento-security {
        min-height: 300px;
    }
    .workflow-list article {
        grid-template-columns: 32px 1fr;
    }
    .step-icon {
        display: none;
    }
    .showcase-window pre {
        height: 350px;
        padding: 20px;
    }
    .showcase-window code {
        font-size: 10px;
    }
    .code-tabs {
        scrollbar-width: none;
    }
    .ai-section {
        width: calc(100% - 20px);
        margin-bottom: 90px;
        padding: 70px 20px;
    }
    .ai-actions,
    .final-actions {
        align-items: stretch;
        flex-direction: column;
    }
    .text-link {
        padding: 10px;
    }
    .final-cta {
        padding-bottom: 90px;
    }
    .final-actions {
        width: 100%;
        text-align: center;
    }
}
@media (prefers-reduced-motion: reduce) {
    .filterable-home *,
    .filterable-home :before,
    .filterable-home :after {
        scroll-behavior: auto !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    .reveal {
        opacity: 1;
        transform: none;
    }
}
</style>
