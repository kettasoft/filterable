<?php

namespace Kettasoft\Filterable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Filterable class.
 * 
 * Static Factory Methods:
 * @method static \Kettasoft\Filterable\Filterable create(\Illuminate\Http\Request|null $request = null) Create new Filterable instance.
 * @method static \Kettasoft\Filterable\Filterable withRequest(\Illuminate\Http\Request $request) Create new Filterable instance with custom Request.
 * 
 * Static Event Methods:
 * @method static void on(string $event, callable $callback) Register a global event listener.
 * @method static void observe(string $filterClass, callable $callback) Register an observer for a specific filter class.
 * @method static void flushListeners() Remove all registered event listeners and observers.
 * @method static array getListeners(string $event) Get all registered listeners for a specific event.
 * @method static array getObservers(string $filterClass) Get all registered observers for a specific filter class.
 * @method static void resetEventManager() Reset the event manager instance.
 * 
 * Static Sorting Methods:
 * @method static void addSorting(string|array $filterable, callable|string|\Kettasoft\Filterable\Foundation\Contracts\Sorting\Invokable $callback, \Illuminate\Http\Request|null $request = null) Add a sorting callback for a specific filterable.
 * @method static \Kettasoft\Filterable\Foundation\Contracts\Sortable|null getSorting(string $filterClass) Get sorting rules for a Filterable class.
 * @method static \Illuminate\Support\Collection aliases(array $aliases) Get all aliases.
 * 
 * Core Filtering Methods:
 * @method static \Kettasoft\Filterable\Foundation\Resources getResources() Get Resources instance.
 * @method static \Kettasoft\Filterable\Foundation\FilterableSettings settings() Get FilterableSettings instance.
 * @method static \Kettasoft\Filterable\Foundation\Invoker|\Illuminate\Database\Eloquent\Builder apply(\Illuminate\Database\Eloquent\Builder|null $builder = null) Apply all filters.
 * @method static \Kettasoft\Filterable\Foundation\Invoker|\Illuminate\Database\Eloquent\Builder filter(\Illuminate\Database\Eloquent\Builder|null $builder = null) Alias name for apply method.
 * @method static \Kettasoft\Filterable\Filterable sorting(callable|string|\Kettasoft\Filterable\Foundation\Contracts\Sorting\Invokable $sorting) Define sorting rules for the current filterable instance.
 * @method static \Kettasoft\Filterable\Filterable shouldReturnQueryBuilder() Should return Query Builder instance when invoke apply.
 * 
 * Model Configuration:
 * @method static \Kettasoft\Filterable\Filterable setModel(\Illuminate\Database\Eloquent\Model|string $model) Set model.
 * @method static \Illuminate\Database\Eloquent\Model|string getModel() Get model.
 * @method static \Illuminate\Database\Eloquent\Model|object|null getModelInstance() Get model instance object.
 * 
 * Conditional & Pipeline Methods:
 * @method static \Kettasoft\Filterable\Filterable when(bool $condition, callable $callback) Apply a callback conditionally and return a new modified instance.
 * @method static \Kettasoft\Filterable\Filterable through(array $pipes) Allow the query to pass through a custom pipeline of pipes (callables).
 * 
 * Engine Configuration:
 * @method static \Kettasoft\Filterable\Filterable useEngin(\Kettasoft\Filterable\Engines\Foundation\Engine|string $engine) Override the default engine for this filterable instance.
 * @method static \Kettasoft\Filterable\Engines\Foundation\Engine getEngin() Get current engine.
 * 
 * Request & Data Management:
 * @method static \Illuminate\Http\Request getRequest() Get the current request instance.
 * @method static \Kettasoft\Filterable\Sanitization\Sanitizer getSanitizerInstance() Get sanitizer instance.
 * @method static \Kettasoft\Filterable\Filterable setData(array $data, bool $override = true) Set manual data injection.
 * @method static mixed getData() Get current data.
 * @method static array getFilterAttributes() Fetch all relevant filters from the filter API class.
 * @method static \Kettasoft\Filterable\Filterable setSource(string $source) Set request source.
 * @method static mixed get(string $key) Retrieve an input item from the request.
 * 
 * Sanitization:
 * @method static \Kettasoft\Filterable\Filterable setSanitizers(array $sanitizers, bool $override = true) Set a new sanitizers classes.
 * @method static \Kettasoft\Filterable\Filterable withoutSanitizers() Disable running sanitizers on the filters.
 * 
 * Value Processing:
 * @method static \Kettasoft\Filterable\Filterable ignoreEmptyValues() Ignore empty or null values.
 * @method static bool hasIgnoredEmptyValues() Check if current filterable class has ignored empty values.
 * 
 * Event Control:
 * @method static \Kettasoft\Filterable\Filterable enableEvents() Enable events for this specific filterable instance.
 * @method static \Kettasoft\Filterable\Filterable disableEvents() Disable events for this specific filterable instance.
 * 
 * Header-Driven Mode:
 * @method static \Kettasoft\Filterable\Filterable withHeaderDrivenMode(mixed $config = []) Enable Header-driven mode per request.
 * 
 * Field Configuration:
 * @method static array getAllowedFields() Get allowed fields to apply filtering.
 * @method static \Kettasoft\Filterable\Filterable setAllowedFields(array $fields, bool $override = false) Define allowed fields to filtering.
 * @method static \Kettasoft\Filterable\Filterable autoSetAllowedFieldsFromModel(bool $override = false) Auto-detect filterable fields from model fillable attributes.
 * 
 * Operator Configuration:
 * @method static array getAllowedOperators() List of supported SQL operators you want to allow when parsing the expressions.
 * @method static \Kettasoft\Filterable\Filterable allowdOperators(array $operators) Set allowed operators and override global operators.
 * 
 * Mode Configuration:
 * @method static \Kettasoft\Filterable\Filterable strict() Enable strict mode in this instance.
 * @method static \Kettasoft\Filterable\Filterable permissive() Enable permissive mode in this instance.
 * @method static mixed isStrict() Check if filter has strict mode.
 * 
 * Field Mapping:
 * @method static array getFieldsMap() Get columns wrapper.
 * @method static \Kettasoft\Filterable\Filterable setFieldsMap(mixed $fields, bool $override = true) Set fields wrapper.
 * 
 * Builder Management:
 * @method static \Illuminate\Database\Eloquent\Builder getBuilder() Get registered filter builder.
 * @method static \Kettasoft\Filterable\Filterable setBuilder(\Illuminate\Database\Eloquent\Builder $builder) Set a new builder.
 * 
 * SQL Export:
 * @method static string toSql(\Illuminate\Database\Eloquent\Builder|null $builder = null, mixed $withBindings = false) Get the SQL representation of the filtered query.
 * 
 * @see \Kettasoft\Filterable\Filterable
 */
class Filterable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'filterable';
    }
}