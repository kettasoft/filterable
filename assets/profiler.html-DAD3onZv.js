import{_ as s,c as a,a as e,o as l}from"./app-Deo94DNs.js";const i={};function t(p,n){return l(),a("div",null,n[0]||(n[0]=[e(`<h1 id="filterable-profiler" tabindex="-1"><a class="header-anchor" href="#filterable-profiler"><span>Filterable Profiler</span></a></h1><h2 id="overview" tabindex="-1"><a class="header-anchor" href="#overview"><span>Overview</span></a></h2><p>The <strong>Filterable Profiler</strong> is a lightweight query observer that runs <strong>only for queries executed via the Filterable layer.</strong></p><h2 id="⚙️-configuration" tabindex="-1"><a class="header-anchor" href="#⚙️-configuration"><span>⚙️ Configuration</span></a></h2><div class="language-php line-numbers-mode" data-highlighter="prismjs" data-ext="php" data-title="php"><pre><code><span class="line"><span class="token string single-quoted-string">&#39;profiler&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">  <span class="token comment">/*</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Enable or Disable Query Profiler</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Turn the profiler on or off globally.</span>
<span class="line">  | Example: FILTERABLE_PROFILER_ENABLED=false</span>
<span class="line">  */</span></span>
<span class="line">  <span class="token string single-quoted-string">&#39;enabled&#39;</span> <span class="token operator">=&gt;</span> <span class="token function">env</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;FILTERABLE_PROFILER_ENABLED&#39;</span><span class="token punctuation">,</span> <span class="token constant boolean">true</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">  <span class="token comment">/*</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Storage Method</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Determines how query profiling data will be stored.</span>
<span class="line">  | Options: &quot;log&quot;, &quot;database&quot;, &quot;none&quot;</span>
<span class="line">  */</span></span>
<span class="line">  <span class="token string single-quoted-string">&#39;store&#39;</span> <span class="token operator">=&gt;</span> <span class="token function">env</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;FILTERABLE_PROFILER_STORE&#39;</span><span class="token punctuation">,</span> <span class="token string single-quoted-string">&#39;log&#39;</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">  <span class="token comment">/*</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Minimum Execution Time (ms)</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Only queries slower than this threshold will be stored.</span>
<span class="line">  */</span></span>
<span class="line">  <span class="token string single-quoted-string">&#39;slow_query_threshold&#39;</span> <span class="token operator">=&gt;</span> <span class="token function">env</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;FILTERABLE_PROFILER_MIN_TIME&#39;</span><span class="token punctuation">,</span> <span class="token number">1.0</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">  <span class="token comment">/*</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Sampling Percentage</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | To reduce overhead, profile only X% of requests.</span>
<span class="line">  | Example: FILTERABLE_PROFILER_SAMPLING=10 (10% of calls)</span>
<span class="line">  */</span></span>
<span class="line">  <span class="token string single-quoted-string">&#39;sampling&#39;</span> <span class="token operator">=&gt;</span> <span class="token function">env</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;FILTERABLE_PROFILER_SAMPLING&#39;</span><span class="token punctuation">,</span> <span class="token number">100</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">  <span class="token comment">/*</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Database Table</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Table name for storing query profiles when using &quot;database&quot;.</span>
<span class="line">  */</span></span>
<span class="line">  <span class="token string single-quoted-string">&#39;table&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;query_profiles&#39;</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">  <span class="token comment">/*</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Log Channel</span>
<span class="line">  |--------------------------------------------------------------------------</span>
<span class="line">  | Log channel to use when &quot;log&quot; storage is enabled.</span>
<span class="line">  */</span></span>
<span class="line">  <span class="token string single-quoted-string">&#39;log_channel&#39;</span> <span class="token operator">=&gt;</span> <span class="token function">env</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;FILTERABLE_PROFILER_LOG_CHANNEL&#39;</span><span class="token punctuation">,</span> <span class="token string single-quoted-string">&#39;daily&#39;</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line"><span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div></div></div><h2 id="🛠-how-it-works" tabindex="-1"><a class="header-anchor" href="#🛠-how-it-works"><span>🛠 How it Works</span></a></h2><ul><li>The profiler <strong>only monitors queries triggered via the Filterable system</strong>.</li><li>At the end of the request, it stores the captured data according to the configured <code>store</code> method (<code>log</code> or <code>database</code>).</li><li>Overhead can be controlled via <code>sampling</code> and <code>slow_query_threshold</code>.</li></ul><h2 id="📌-example-usage" tabindex="-1"><a class="header-anchor" href="#📌-example-usage"><span>📌 Example Usage</span></a></h2><p>Enable the profiler in .env:</p><div class="language-dotenv line-numbers-mode" data-highlighter="prismjs" data-ext="dotenv" data-title="dotenv"><pre><code><span class="line">FILTERABLE_PROFILER_ENABLED=true</span>
<span class="line">FILTERABLE_PROFILER_STORE=log</span>
<span class="line">FILTERABLE_PROFILER_MIN_TIME=5</span>
<span class="line">FILTERABLE_PROFILER_SAMPLING=50</span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div></div></div><p>Result:</p><ul><li>50% of requests are sampled.</li><li>Only queries slower than 5ms are logged.</li><li>Data is stored in the log channel.</li></ul>`,12)]))}const c=s(i,[["render",t],["__file","profiler.html.vue"]]),r=JSON.parse('{"path":"/profiler.html","title":"Filterable Profiler","lang":"en-US","frontmatter":{},"headers":[{"level":2,"title":"Overview","slug":"overview","link":"#overview","children":[]},{"level":2,"title":"⚙️ Configuration","slug":"⚙️-configuration","link":"#⚙️-configuration","children":[]},{"level":2,"title":"🛠 How it Works","slug":"🛠-how-it-works","link":"#🛠-how-it-works","children":[]},{"level":2,"title":"📌 Example Usage","slug":"📌-example-usage","link":"#📌-example-usage","children":[]}],"git":{"updatedTime":1755451918000,"contributors":[{"name":"Abdalrhman Emad Saad","email":"a.emad@codeclouders.com","commits":1}]},"filePathRelative":"profiler.md"}');export{c as comp,r as data};
