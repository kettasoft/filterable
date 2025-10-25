import{_ as n,c as a,a as e,o as i}from"./app-BzrB1Tdi.js";const l={};function p(t,s){return i(),a("div",null,s[0]||(s[0]=[e(`<h1 id="📦-installation" tabindex="-1"><a class="header-anchor" href="#📦-installation"><span>📦 Installation</span></a></h1><p>To install <strong>Filterable</strong>, simply use Composer to add it to your project:</p><div class="language-bash line-numbers-mode" data-highlighter="prismjs" data-ext="sh" data-title="sh"><pre><code><span class="line"><span class="token function">composer</span> require kettasoft/filterable</span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div></div></div><h3 id="service-provider-registration" tabindex="-1"><a class="header-anchor" href="#service-provider-registration"><span><strong>Service Provider Registration</strong></span></a></h3><p>Add the following line to the <strong><code>providers</code></strong> array in <strong><code>config/app.php</code></strong>:</p><div class="language-php line-numbers-mode" data-highlighter="prismjs" data-ext="php" data-title="php"><pre><code><span class="line"><span class="token string single-quoted-string">&#39;providers&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line"></span>
<span class="line">    <span class="token operator">...</span></span>
<span class="line"></span>
<span class="line">    <span class="token class-name class-name-fully-qualified static-context">Kettasoft<span class="token punctuation">\\</span>Filterable<span class="token punctuation">\\</span>Providers<span class="token punctuation">\\</span>FilterableServiceProvider</span><span class="token operator">::</span><span class="token keyword">class</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line"><span class="token punctuation">]</span><span class="token punctuation">;</span></span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div></div></div><h3 id="publishing-configuration-and-stubs" tabindex="-1"><a class="header-anchor" href="#publishing-configuration-and-stubs"><span><strong>Publishing Configuration and Stubs</strong></span></a></h3><p>After installation, you can publish the configuration file and stubs with the following commands:</p><div class="language-bash line-numbers-mode" data-highlighter="prismjs" data-ext="sh" data-title="sh"><pre><code><span class="line">php artisan vendor:publish <span class="token parameter variable">--provider</span><span class="token operator">=</span><span class="token string">&quot;Kettasoft\\Filterable\\Providers\\FilterableServiceProvider&quot;</span> <span class="token parameter variable">--tag</span><span class="token operator">=</span><span class="token string">&quot;config&quot;</span></span>
<span class="line">php artisan vendor:publish <span class="token parameter variable">--provider</span><span class="token operator">=</span><span class="token string">&quot;Kettasoft\\Filterable\\Providers\\FilterableServiceProvider&quot;</span> <span class="token parameter variable">--tag</span><span class="token operator">=</span><span class="token string">&quot;stubs&quot;</span></span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div></div></div><p>These are the contents of the default config file that will be published:</p><div class="language-php line-numbers-mode" data-highlighter="prismjs" data-ext="php" data-title="php"><pre><code><span class="line"><span class="token php language-php"><span class="token delimiter important">&lt;?php</span></span>
<span class="line"></span>
<span class="line"><span class="token keyword">return</span> <span class="token punctuation">[</span></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Eloquent Filter Settings</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | This is the namespace all you Eloquent Model Filters will reside</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;namespace&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;App\\\\Http\\\\Filters&#39;</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Path of saving new filters</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | This is the namespace all you Eloquent Model Filters will reside</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;save_filters_at&#39;</span> <span class="token operator">=&gt;</span> <span class="token function">app_path</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;Http/Filters&#39;</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Default Request Key</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | The query string key to look for filter inputs automatically from requests.</span>
<span class="line">    | Example: /posts?filter[title]=test</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;filter_key&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;filter&#39;</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Default Request Source.</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | By default, filters will read query parameters from the request instance.</span>
<span class="line">    | You can change the source here if you want to use another source (e.g. JSON body).</span>
<span class="line">    | Options: &#39;query&#39;, &#39;input&#39;, &#39;json&#39;</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;request_source&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;query&#39;</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Filter Aliases</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | Define short, human-friendly aliases for your filter classes.</span>
<span class="line">    | These aliases allow you to reference filters using simple names</span>
<span class="line">    | when building dynamic or automatic filter logic, instead of full class paths.</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;aliases&#39;</span> <span class="token operator">=&gt;</span> <span class="token class-name static-context">Filterable</span><span class="token operator">::</span><span class="token function">aliases</span><span class="token punctuation">(</span><span class="token punctuation">[</span></span>
<span class="line">        <span class="token comment">// &#39;users&#39; =&gt; App\\Http\\Filters\\UserFilter::class</span></span>
<span class="line">    <span class="token punctuation">]</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Default Filter Engine</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | The filter engine that will be used by default when no engine is specified</span>
<span class="line">    | explicitly. You can change it to any of the engines listed in the</span>
<span class="line">    | &quot;engines&quot; section below.</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;default_engine&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;invokable&#39;</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Filter Engines</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | Define all available filter engines in your application. Each engine</span>
<span class="line">    | contains its own options that control its behavior and logic.</span>
<span class="line">    | You can create your own custom engines and register them here.</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;engines&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Invokable Filter Engine</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        |</span>
<span class="line">        | The Invokable Engine provides a powerful way to dynamically map incomming reuqest parameters to corresponding methods in a filter class.</span>
<span class="line">        |</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;invokable&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Egnore empty values</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | If &#39;true&#39; filters with null or empty string values will be ignored.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;ignore_empty_values&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">false</span><span class="token punctuation">,</span></span>
<span class="line">        <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Tree Filter Engine</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        |</span>
<span class="line">        | This engine uses a tree-like structure to combine conditions using</span>
<span class="line">        | logical operators (AND/OR). It&#39;s useful for building complex queries</span>
<span class="line">        | with nested conditions.</span>
<span class="line">        |</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;tree&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Strict Mode</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | When enabled, if any filter key is not allowed, the entire filtering process</span>
<span class="line">            | will stop and throw exception. Otherwise, it will ignore unallowed filters.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;strict&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">true</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Allowed SQL Operators</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | List of supported SQL operators you want to allow when parsing</span>
<span class="line">            | the expressions.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;allowed_operators&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;eq&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;neq&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;!=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;gt&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&gt;&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;lt&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&lt;&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;gte&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&gt;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;lte&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&lt;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;like&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;like&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;nlike&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;not like&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;in&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;in&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;nin&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;not in&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;null&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;is null&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;notnull&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;is not null&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;between&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;between&#39;</span><span class="token punctuation">,</span></span>
<span class="line">            <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Default Operator</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | Default operator when request dosen&#39;t has operator.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;default_operator&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;=&#39;</span><span class="token punctuation">,</span> <span class="token comment">// =</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | ignore empty values</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | If &#39;true&#39; filters with null or empty string values will be ignored.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;ignore_empty_values&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">false</span><span class="token punctuation">,</span></span>
<span class="line">        <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Ruleset Filter Engine</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        |</span>
<span class="line">        | A simple engine that applies a simple queries independently. This</span>
<span class="line">        | is great when your filters are not deeply nested or hierarchical.</span>
<span class="line">        |</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;ruleset&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Strict Mode</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | When enabled, if any filter key is not allowed, the entire filtering process</span>
<span class="line">            | will stop and throw exception. Otherwise, it will ignore unallowed filters.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;strict&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">true</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Allowed Fields</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | Specify which fields are allowed to be filtered. Leave empty</span>
<span class="line">            | to allow all fields.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;allowed_fields&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span><span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Allowed SQL Operators</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | List of supported SQL operators you want to allow when parsing</span>
<span class="line">            | the expressions.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;allowed_operators&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;eq&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;neq&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;!=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;gt&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&gt;&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;lt&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&lt;&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;gte&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&gt;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;lte&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&lt;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;like&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;like&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;nlike&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;not like&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;in&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;in&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;nin&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;not in&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;null&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;is null&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;notnull&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;is not null&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;between&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;between&#39;</span><span class="token punctuation">,</span></span>
<span class="line">            <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Default Operator</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | Default operator when request dosen&#39;t has operator.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;default_operator&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;eq&#39;</span><span class="token punctuation">,</span> <span class="token comment">// =</span></span>
<span class="line">        <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | SQL Expression Filter Engine</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        |</span>
<span class="line">        | Converts filters into raw SQL expressions. Ideal when you need</span>
<span class="line">        | fine-grained control over generated SQL queries.</span>
<span class="line">        |</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;expression&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | ignore empty values</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | If &#39;true&#39; filters with null or empty string values will be ignored.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;ignore_empty_values&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">false</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Allowed SQL Operators</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | List of supported SQL operators you want to allow when parsing</span>
<span class="line">            | the expressions.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;allowed_operators&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;eq&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;neq&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;!=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;gt&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&gt;&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;lt&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&lt;&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;gte&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&gt;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;lte&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;&lt;=&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;like&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;like&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;nlike&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;not like&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;in&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;in&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;nin&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;not in&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;null&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;is null&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;notnull&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;is not null&#39;</span><span class="token punctuation">,</span></span>
<span class="line">                <span class="token string single-quoted-string">&#39;between&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;between&#39;</span><span class="token punctuation">,</span></span>
<span class="line">            <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Default Operator</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | Default operator when request dosen&#39;t has operator.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;default_operator&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;eq&#39;</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Validate Columns</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | Whether to check if a column exists in the schema before</span>
<span class="line">            | building the SQL expression.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;validate_columns&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">true</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Allowed Fields</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            |</span>
<span class="line">            | Specify which fields are allowed to be filtered. Leave empty</span>
<span class="line">            | to allow all fields.</span>
<span class="line">            |</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;allowed_fields&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span><span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">            <span class="token comment">/*</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | Strict Mode</span>
<span class="line">            |--------------------------------------------------------------------------</span>
<span class="line">            | If true, the package will throw an exception if a field</span>
<span class="line">            | is not allowed in the allowed fields.</span>
<span class="line">            */</span></span>
<span class="line">            <span class="token string single-quoted-string">&#39;strict&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">true</span></span>
<span class="line">        <span class="token punctuation">]</span></span>
<span class="line">    <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Custom generator stub</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | If you want to override the default stub this package provides</span>
<span class="line">    | you can enter the path to your own at this point</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;generator&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;stubs&#39;</span> <span class="token operator">=&gt;</span> <span class="token function">base_path</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;vendor/kettasoft/filterable/src/stubs/&#39;</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line">    <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Default Paginator Limit For \`paginateFilter\` and \`simplePaginateFilter\`</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    |</span>
<span class="line">    | Set paginate limit</span>
<span class="line">    |</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;paginate_limit&#39;</span> <span class="token operator">=&gt;</span> <span class="token function">env</span><span class="token punctuation">(</span><span class="token string single-quoted-string">&#39;PAGINATION_LIMIT_DEFAULT&#39;</span><span class="token punctuation">,</span> <span class="token number">15</span><span class="token punctuation">)</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">    <span class="token comment">/*</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Header Driven Filter Mode</span>
<span class="line">    |--------------------------------------------------------------------------</span>
<span class="line">    | Allows dynamically selecting the filter engine via HTTP headers.</span>
<span class="line">    | When enabled, the package will check for the specified header and use</span>
<span class="line">    | its value to determine which filter engine to apply for that request.</span>
<span class="line">    |</span>
<span class="line">    | This is useful when you need different filtering behavior for:</span>
<span class="line">    | - Different client types (mobile/web)</span>
<span class="line">    | - API versions</span>
<span class="line">    | - Special request cases</span>
<span class="line">    */</span></span>
<span class="line">    <span class="token string single-quoted-string">&#39;header_driven_mode&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Enable Header Driven Mode</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | When true, the package will check for the filter mode header</span>
<span class="line">        | and attempt to use the specified engine if valid.</span>
<span class="line">        |</span>
<span class="line">        | Set to false to completely ignore the header.</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;enabled&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">false</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Filter Mode Header Name</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | The HTTP header name that will be checked for engine selection.</span>
<span class="line">        |</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;header_name&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;X-Filter-Mode&#39;</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Available Engines Whitelist</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | List of engine names that can be specified in the header.</span>
<span class="line">        | Empty array means all configured engines are allowed.</span>
<span class="line">        |</span>
<span class="line">        | Example: [&#39;dynamic&#39;, &#39;tree&#39;] would only allow these two engines</span>
<span class="line">        | via header selection.</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;allowed_engines&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span><span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Engine Name Mapping</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Maps header values to actual engine names.</span>
<span class="line">        | Useful when you want to expose different names to clients.</span>
<span class="line">        |</span>
<span class="line">        | Example:</span>
<span class="line">        | &#39;engine_map&#39; =&gt; [</span>
<span class="line">        |     &#39;simple&#39; =&gt; &#39;ruleset&#39;,</span>
<span class="line">        |     &#39;advanced&#39; =&gt; &#39;dynamic&#39;,</span>
<span class="line">        |     &#39;full&#39; =&gt; &#39;expression&#39;</span>
<span class="line">        | ]</span>
<span class="line">        |</span>
<span class="line">        | Header value &#39;simple&#39; would use the &#39;ruleset&#39; engine</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;engine_map&#39;</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span><span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"></span>
<span class="line">        <span class="token comment">/*</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Fallback Strategy</span>
<span class="line">        |--------------------------------------------------------------------------</span>
<span class="line">        | Determines behavior when an invalid engine is specified:</span>
<span class="line">        |</span>
<span class="line">        | &#39;default&#39; - Silently falls back to default engine</span>
<span class="line">        | &#39;error&#39; - Returns 400 Bad Request response</span>
<span class="line">        |</span>
<span class="line">        | Note: Always validates against configured engines in &#39;engines&#39; section.</span>
<span class="line">        */</span></span>
<span class="line">        <span class="token string single-quoted-string">&#39;fallback_strategy&#39;</span> <span class="token operator">=&gt;</span> <span class="token string single-quoted-string">&#39;default&#39;</span><span class="token punctuation">,</span></span>
<span class="line">    <span class="token punctuation">]</span><span class="token punctuation">,</span></span>
<span class="line"><span class="token punctuation">]</span><span class="token punctuation">;</span></span>
<span class="line"></span>
<span class="line"></span>
<span class="line"></span></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div></div></div><hr><h3 id="step-1-add-the-filterable-trait-to-your-model" tabindex="-1"><a class="header-anchor" href="#step-1-add-the-filterable-trait-to-your-model"><span><strong>Step 1: Add the <code>Filterable</code> Trait to Your Model</strong></span></a></h3><p>To enable filtering on your model, you need to include the <code>Filterable</code> trait in the model you want to apply filters on.</p><div class="language-php line-numbers-mode" data-highlighter="prismjs" data-ext="php" data-title="php"><pre><code><span class="line"><span class="token php language-php"><span class="token delimiter important">&lt;?php</span></span>
<span class="line"></span>
<span class="line"><span class="token keyword">use</span> <span class="token package">Kettasoft<span class="token punctuation">\\</span>Filterable<span class="token punctuation">\\</span>Filterable</span><span class="token punctuation">;</span></span>
<span class="line"></span>
<span class="line"><span class="token keyword">class</span> <span class="token class-name-definition class-name">Post</span> <span class="token keyword">extends</span> <span class="token class-name">Model</span></span>
<span class="line"><span class="token punctuation">{</span></span>
<span class="line">    <span class="token keyword">use</span> <span class="token package">Filterable</span><span class="token punctuation">;</span></span>
<span class="line"><span class="token punctuation">}</span></span>
<span class="line"></span></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div><div class="line-number"></div></div></div><hr><h3 id="step-2-create-a-custom-filter-class" tabindex="-1"><a class="header-anchor" href="#step-2-create-a-custom-filter-class"><span><strong>Step 2: Create a Custom Filter Class</strong></span></a></h3><p>You can generate a custom filter class for your model by running the artisan command:</p><div class="language-bash line-numbers-mode" data-highlighter="prismjs" data-ext="sh" data-title="sh"><pre><code><span class="line">php artisan kettasoft:make-filter PostFilter <span class="token parameter variable">--filters</span><span class="token operator">=</span>title,status</span>
<span class="line"></span></code></pre><div class="line-numbers" aria-hidden="true" style="counter-reset:line-number 0;"><div class="line-number"></div></div></div><p>This command will generate a filter class where you can define custom filter methods.</p><hr>`,21)]))}const r=n(l,[["render",p],["__file","installation.html.vue"]]),o=JSON.parse('{"path":"/installation.html","title":"📦 Installation","lang":"en-US","frontmatter":{},"headers":[{"level":3,"title":"Service Provider Registration","slug":"service-provider-registration","link":"#service-provider-registration","children":[]},{"level":3,"title":"Publishing Configuration and Stubs","slug":"publishing-configuration-and-stubs","link":"#publishing-configuration-and-stubs","children":[]},{"level":3,"title":"Step 1: Add the Filterable Trait to Your Model","slug":"step-1-add-the-filterable-trait-to-your-model","link":"#step-1-add-the-filterable-trait-to-your-model","children":[]},{"level":3,"title":"Step 2: Create a Custom Filter Class","slug":"step-2-create-a-custom-filter-class","link":"#step-2-create-a-custom-filter-class","children":[]}],"git":{"updatedTime":1761374175000,"contributors":[{"name":"Abdalrhman Emad Saad","email":"a.emad@codeclouders.com","commits":4},{"name":"kettasoft","email":"kettasoft@gmail.com","commits":1}]},"filePathRelative":"installation.md"}');export{r as comp,o as data};
