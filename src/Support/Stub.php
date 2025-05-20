<?php

namespace Kettasoft\Filterable\Support;

class Stub implements \Stringable
{
    /**
     * The base path of stub file.
     *
     * @var null|string
     */
    protected string $stub;

    /**
     * The replacements array.
     *
     * @var array
     */
    protected array $replacements;

    /**
     * The base path of stub file.
     *
     * @var null|string
     */
    protected static $basePath = null;

    /**
     * The contructor.
     *
     * @param string $path
     * @param array  $replaces
     */
    public function __construct($path, $replacements)
    {
        $this->stub = $path;
        $this->replacements = $replacements;
    }

    /**
     * Create new self instance.
     *
     * @param string $path
     * @param array  $replaces
     *
     * @return self
     */
    public static function create($path, $replacements)
    {
        return new self($path, $replacements);
    }

    /**
     * Get stub path.
     *
     * @return string
     */
    public function getPath()
    {
        $path = static::getBasePath() . $this->stub;

        return file_exists($path) ? $path : config('filterable.generator.stub') . $this->stub;
    }

    /**
     * Set base path.
     *
     * @param string $path
     */
    public static function setBasePath($path)
    {
        static::$basePath = $path;
    }

    /**
     * Get base path.
     *
     * @return string|null
     */
    public static function getBasePath()
    {
        return static::$basePath;
    }

    /**
     * Get stub contents.
     *
     * @return array|bool|string
     */
    public function getContents(): array|bool|string
    {
        $contents = file_get_contents($this->getPath());

        foreach ($this->replacements as $search => $replace) {
            $contents = str_replace('$$' . strtoupper($search) . '$$', $replace, $contents);
        }

        return $contents;
    }

    public function render(): array|bool|string
    {
        return $this->getContents();
    }

    /**
     * Save stub to specific path.
     *
     * @param string $path
     * @param string $filename
     *
     * @return bool|int
     */
    public function saveTo($path, $filename): bool|int
    {
        if (!is_dir($path)) {
            mkdir($path);
        }

        return file_put_contents($path . '/' . $filename, $this->getContents());
    }

    /**
     * Set replacements array.
     *
     * @param array $replaces
     *
     * @return $this
     */
    public function replace(array $replacements = [])
    {
        $this->replacements = $replacements;

        return $this;
    }

    /**
     * Get replacements.
     *
     * @return array
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }

    /**
     * Handle magic method __toString.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
