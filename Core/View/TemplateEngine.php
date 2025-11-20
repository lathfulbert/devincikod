<?php

namespace App\Core\View;

class TemplateEngine
{
    protected string $cachePath;
    protected array $sections = [];
    protected array $sectionStack = [];
    protected string $extends = '';

    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;

        // Ensure cache directory exists
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Compile a template file and return the path to the compiled version.
     */
    public function compile(string $templatePath): string
    {
        $compiledPath = $this->getCompiledPath($templatePath);

        // Check if recompilation is needed
        if ($this->needsRecompilation($templatePath, $compiledPath)) {
            $contents = file_get_contents($templatePath);
            $compiled = $this->compileString($contents);
            file_put_contents($compiledPath, $compiled);
        }

        return $compiledPath;
    }

    /**
     * Compile a template string.
     */
    public function compileString(string $template): string
    {
        $result = $template;

        // Compile in order of precedence
        $result = $this->compileComments($result);
        $result = $this->compileEchos($result);
        $result = $this->compilePhp($result);

        // Directives
        $result = $this->compileExtends($result);
        $result = $this->compileSection($result);
        $result = $this->compileYield($result);
        $result = $this->compileInclude($result);

        // Control structures
        $result = $this->compileIf($result);
        $result = $this->compileElse($result);
        $result = $this->compileEndif($result);
        $result = $this->compileForeach($result);
        $result = $this->compileEndforeach($result);
        $result = $this->compileFor($result);
        $result = $this->compileEndfor($result);
        $result = $this->compileWhile($result);
        $result = $this->compileEndwhile($result);

        return $result;
    }

    protected function compileExtends(string $value): string
    {
        // Match @extends('layout')
        // We replace it with PHP code that sets the extends property on the engine
        return preg_replace('/@extends\s*\([\'"](.+?)[\'"]\)/', '<?php $this->engine->setExtends(\'$1\'); ?>', $value);
    }

    protected function compileSection(string $value): string
    {
        // @section('name')
        $value = preg_replace('/@section\s*\([\'"](.+?)[\'"]\)/', '<?php $this->startSection(\'$1\'); ?>', $value);
        // @endsection
        $value = preg_replace('/@endsection/', '<?php $this->endSection(); ?>', $value);
        return $value;
    }

    protected function compileYield(string $value): string
    {
        // @yield('name')
        return preg_replace('/@yield\s*\([\'"](.+?)[\'"]\)/', '<?= $this->yieldSection(\'$1\') ?>', $value);
    }

    protected function compileInclude(string $value): string
    {
        // @include('view')
        return preg_replace('/@include\s*\([\'"](.+?)[\'"]\)/', '<?php echo $this->make(\'$1\', get_defined_vars()); ?>', $value);
    }

    /**
     * Compile comments.
     */
    protected function compileComments(string $value): string
    {
        return preg_replace('/\{\{--(.*?)--\}\}/s', '', $value);
    }

    /**
     * Compile echoing statements.
     */
    protected function compileEchos(string $value): string
    {
        // Raw echo {!! !!}
        $value = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?= $1 ?>', $value);

        // Escaped echo {{ }}
        $value = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?= e($1) ?>', $value);

        return $value;
    }

    /**
     * Compile @if statements.
     */
    protected function compileIf(string $value): string
    {
        return preg_replace('/\B@if\s*\((.*)\)/', '<?php if($1): ?>', $value);
    }

    /**
     * Compile @elseif statements.
     */
    protected function compileElse(string $value): string
    {
        $value = preg_replace('/\B@elseif\s*\((.*)\)/', '<?php elseif($1): ?>', $value);
        $value = preg_replace('/\B@else/', '<?php else: ?>', $value);
        return $value;
    }

    /**
     * Compile @endif statements.
     */
    protected function compileEndif(string $value): string
    {
        return preg_replace('/\B@endif/', '<?php endif; ?>', $value);
    }

    /**
     * Compile @foreach statements.
     */
    protected function compileForeach(string $value): string
    {
        return preg_replace('/\B@foreach\s*\((.*)\)/', '<?php foreach($1): ?>', $value);
    }

    /**
     * Compile @endforeach statements.
     */
    protected function compileEndforeach(string $value): string
    {
        return preg_replace('/\B@endforeach/', '<?php endforeach; ?>', $value);
    }

    /**
     * Compile @for statements.
     */
    protected function compileFor(string $value): string
    {
        return preg_replace('/\B@for\s*\((.*)\)/', '<?php for($1): ?>', $value);
    }

    /**
     * Compile @endfor statements.
     */
    protected function compileEndfor(string $value): string
    {
        return preg_replace('/\B@endfor/', '<?php endfor; ?>', $value);
    }

    /**
     * Compile @while statements.
     */
    protected function compileWhile(string $value): string
    {
        return preg_replace('/\B@while\s*\((.*)\)/', '<?php while($1): ?>', $value);
    }

    /**
     * Compile @endwhile statements.
     */
    protected function compileEndwhile(string $value): string
    {
        return preg_replace('/\B@endwhile/', '<?php endwhile; ?>', $value);
    }

    /**
     * Compile @php statements.
     */
    protected function compilePhp(string $value): string
    {
        return preg_replace('/\B@php/', '<?php', $value);
    }

    /**
     * Get the path to the compiled version of a template.
     */
    protected function getCompiledPath(string $templatePath): string
    {
        return $this->cachePath . '/' . md5($templatePath) . '.php';
    }

    /**
     * Determine if a template needs recompilation.
     */
    protected function needsRecompilation(string $templatePath, string $compiledPath): bool
    {
        if (!file_exists($compiledPath)) {
            return true;
        }

        return filemtime($templatePath) > filemtime($compiledPath);
    }

    /**
     * Get all sections.
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * Start a section.
     */
    public function startSection(string $name): void
    {
        $this->sectionStack[] = $name;
        ob_start();
    }

    /**
     * End a section.
     */
    public function endSection(): void
    {
        if (empty($this->sectionStack)) {
            return;
        }

        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
    }

    /**
     * Yield a section.
     */
    public function yieldSection(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Set the layout to extend.
     */
    public function setExtends(string $layout): void
    {
        $this->extends = $layout;
    }

    /**
     * Get the layout to extend.
     */
    public function getExtends(): string
    {
        return $this->extends;
    }

    /**
     * Reset the engine state.
     */
    public function reset(): void
    {
        $this->extends = '';
        $this->sections = [];
        $this->sectionStack = [];
    }
}
