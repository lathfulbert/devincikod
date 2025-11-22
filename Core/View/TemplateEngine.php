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
        $result = $this->compileUnless($result);
        $result = $this->compileIsset($result);
        $result = $this->compileEmpty($result);
        $result = $this->compileAuth($result);
        $result = $this->compileGuest($result);
        $result = $this->compileCan($result);
        $result = $this->compileRole($result);
        $result = $this->compileSwitch($result);
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
        // Match @include('view', ['var' => 'value']) or @include('view')
        $pattern = '/@include\s*\(\s*[\'"](.+?)[\'"]\s*(?:,\s*(\[.*?\]))?\s*\)/s';

        return preg_replace_callback($pattern, function ($matches) {
            $view = $matches[1];
            $vars = $matches[2] ?? 'get_defined_vars()';

            if ($vars !== 'get_defined_vars()') {
                // Merge with current vars
                return "<?php echo \$this->make('$view', array_merge(get_defined_vars(), $vars)); ?>";
            }

            return "<?php echo \$this->make('$view', get_defined_vars()); ?>";
        }, $value);
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
     * Compile @unless statements.
     */
    protected function compileUnless(string $value): string
    {
        $value = preg_replace('/\B@unless\s*\((.*)\)/', '<?php if(!($1)): ?>', $value);
        $value = preg_replace('/\B@endunless/', '<?php endif; ?>', $value);
        return $value;
    }

    /**
     * Compile @isset statements.
     */
    protected function compileIsset(string $value): string
    {
        $value = preg_replace('/\B@isset\s*\((.*)\)/', '<?php if(isset($1)): ?>', $value);
        $value = preg_replace('/\B@endisset/', '<?php endif; ?>', $value);
        return $value;
    }

    /**
     * Compile @empty statements.
     */
    protected function compileEmpty(string $value): string
    {
        $value = preg_replace('/\B@empty\s*\((.*)\)/', '<?php if(empty($1)): ?>', $value);
        $value = preg_replace('/\B@endempty/', '<?php endif; ?>', $value);
        return $value;
    }

    /**
     * Compile @auth statements.
     */
    protected function compileAuth(string $value): string
    {
        $value = preg_replace('/\B@auth/', '<?php if(auth()->check()): ?>', $value);
        $value = preg_replace('/\B@endauth/', '<?php endif; ?>', $value);
        return $value;
    }

    /**
     * Compile @guest statements.
     */
    protected function compileGuest(string $value): string
    {
        $value = preg_replace('/\B@guest/', '<?php if(!auth()->check()): ?>', $value);
        $value = preg_replace('/\B@endguest/', '<?php endif; ?>', $value);
        return $value;
    }

    /**
     * Compile @can statements.
     */
    protected function compileCan(string $value): string
    {
        // @can('permission', $model)
        $value = preg_replace_callback(
            '/\B@can\s*\(\s*[\'"](.+?)[\'"]\s*(?:,\s*(.+?))?\s*\)/',
            function ($matches) {
                $permission = $matches[1];
                $model = $matches[2] ?? 'null';
                return "<?php if(can('$permission', $model)): ?>";
            },
            $value
        );
        $value = preg_replace('/\B@endcan/', '<?php endif; ?>', $value);

        // @cannot('permission', $model)
        $value = preg_replace_callback(
            '/\B@cannot\s*\(\s*[\'"](.+?)[\'"]\s*(?:,\s*(.+?))?\s*\)/',
            function ($matches) {
                $permission = $matches[1];
                $model = $matches[2] ?? 'null';
                return "<?php if(!can('$permission', $model)): ?>";
            },
            $value
        );
        $value = preg_replace('/\B@endcannot/', '<?php endif; ?>', $value);

        return $value;
    }

    /**
     * Compile @role statements.
     */
    protected function compileRole(string $value): string
    {
        $value = preg_replace('/\B@role\s*\((.*)\)/', '<?php if(has_role($1)): ?>', $value);
        $value = preg_replace('/\B@endrole/', '<?php endif; ?>', $value);
        return $value;
    }

    /**
     * Compile @switch statements.
     */
    protected function compileSwitch(string $value): string
    {
        // @switch($variable)
        $value = preg_replace('/\B@switch\s*\((.*)\)/', '<?php switch($1): ?>', $value);

        // @case(value)
        $value = preg_replace('/\B@case\s*\((.*)\)/', '<?php case $1: ?>', $value);

        // @break
        $value = preg_replace('/\B@break/', '<?php break; ?>', $value);

        // @default
        $value = preg_replace('/\B@default/', '<?php default: ?>', $value);

        // @endswitch
        $value = preg_replace('/\B@endswitch/', '<?php endswitch; ?>', $value);

        return $value;
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
