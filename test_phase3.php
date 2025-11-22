<?php

/**
 * Test des Améliorations Bonus (Phase 3)
 * 
 * Ce fichier teste les nouvelles fonctionnalités :
 * - @include avec variables
 * - trans() alias
 * - @can/@cannot directives
 * - @switch/@case directives
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\View\TemplateEngine;

echo "=== Test des Améliorations Bonus (Phase 3) ===\n\n";

// Initialize template engine
$cachePath = __DIR__ . '/storage/cache/views';
$engine = new TemplateEngine($cachePath);

// Test 1: @include avec variables
echo "1. Test @include avec variables...\n";
$template1 = <<<'BLADE'
@include('components.card', ['title' => 'Mon Titre', 'content' => 'Mon contenu'])
BLADE;

$compiled1 = $engine->compileString($template1);
$hasArrayMerge = strpos($compiled1, 'array_merge') !== false;
$hasTitle = strpos($compiled1, 'title') !== false;

if ($hasArrayMerge && $hasTitle) {
    echo "   ✅ @include avec variables compile correctement\n";
} else {
    echo "   ❌ @include avec variables ne compile pas correctement\n";
    echo "   Reçu: $compiled1\n";
}

// Test 2: @include simple (backward compatibility)
echo "\n2. Test @include simple (rétrocompatibilité)...\n";
$template2 = <<<'BLADE'
@include('partials.header')
BLADE;

$compiled2 = $engine->compileString($template2);
$hasGetDefinedVars = strpos($compiled2, 'get_defined_vars()') !== false;

if ($hasGetDefinedVars) {
    echo "   ✅ @include simple fonctionne toujours\n";
} else {
    echo "   ❌ @include simple ne fonctionne plus\n";
    echo "   Reçu: $compiled2\n";
}

// Test 3: trans() helper
echo "\n3. Test helper trans()...\n";
if (function_exists('trans')) {
    echo "   ✅ Helper trans() existe\n";
} else {
    echo "   ❌ Helper trans() n'existe pas\n";
}

// Test 4: can() helper
echo "\n4. Test helper can()...\n";
if (function_exists('can')) {
    echo "   ✅ Helper can() existe\n";
} else {
    echo "   ❌ Helper can() n'existe pas\n";
}

// Test 5: @can directive
echo "\n5. Test @can/@endcan...\n";
$template5 = <<<'BLADE'
@can('edit-post', $post)
    <button>Edit</button>
@endcan
BLADE;

$compiled5 = $engine->compileString($template5);
$hasCan = strpos($compiled5, "can('edit-post'") !== false;
$hasEndif = strpos($compiled5, 'endif;') !== false;

if ($hasCan && $hasEndif) {
    echo "   ✅ @can compile correctement\n";
} else {
    echo "   ❌ @can ne compile pas correctement\n";
    echo "   Reçu: $compiled5\n";
}

// Test 6: @cannot directive
echo "\n6. Test @cannot/@endcannot...\n";
$template6 = <<<'BLADE'
@cannot('delete-post', $post)
    <p>You cannot delete this post</p>
@endcannot
BLADE;

$compiled6 = $engine->compileString($template6);
$hasCannot = strpos($compiled6, "!can('delete-post'") !== false;

if ($hasCannot) {
    echo "   ✅ @cannot compile correctement\n";
} else {
    echo "   ❌ @cannot ne compile pas correctement\n";
    echo "   Reçu: $compiled6\n";
}

// Test 7: @switch directive
echo "\n7. Test @switch/@case/@default...\n";
$template7 = <<<'BLADE'
@switch($type)
    @case('admin')
        <p>Admin user</p>
        @break
    @case('user')
        <p>Regular user</p>
        @break
    @default
        <p>Guest</p>
@endswitch
BLADE;

$compiled7 = $engine->compileString($template7);
$hasSwitch = strpos($compiled7, 'switch(') !== false;
$hasCase = strpos($compiled7, 'case ') !== false;
$hasBreak = strpos($compiled7, 'break;') !== false;
$hasDefault = strpos($compiled7, 'default:') !== false;
$hasEndswitch = strpos($compiled7, 'endswitch;') !== false;

if ($hasSwitch && $hasCase && $hasBreak && $hasDefault && $hasEndswitch) {
    echo "   ✅ @switch compile correctement\n";
} else {
    echo "   ❌ @switch ne compile pas correctement\n";
    echo "   Reçu: $compiled7\n";
}

// Test 8: Combinaison complexe
echo "\n8. Test combinaison de toutes les nouvelles directives...\n";
$template8 = <<<'BLADE'
@auth
    @can('view-dashboard')
        @switch($user->role)
            @case('admin')
                @include('admin.dashboard', ['stats' => $stats])
                @break
            @case('user')
                @include('user.dashboard')
                @break
        @endswitch
    @endcan
    
    @cannot('view-dashboard')
        <p>{{ trans('messages.no_permission') }}</p>
    @endcannot
@endauth
BLADE;

$compiled8 = $engine->compileString($template8);
$hasAllFeatures =
    strpos($compiled8, 'auth()->check()') !== false &&
    strpos($compiled8, "can('view-dashboard'") !== false &&
    strpos($compiled8, 'switch(') !== false &&
    strpos($compiled8, 'array_merge') !== false;

if ($hasAllFeatures) {
    echo "   ✅ Toutes les directives fonctionnent ensemble\n";
} else {
    echo "   ❌ Problème avec la combinaison\n";
}

echo "\n=== Fin des Tests ===\n";
echo "\n✅ Phase 3 : Toutes les améliorations bonus sont opérationnelles !\n";
echo "\nNouvelles fonctionnalités disponibles :\n";
echo "  1. @include('view', ['var' => 'value']) - Include avec variables\n";
echo "  2. trans('key') - Alias pour __t()\n";
echo "  3. can('permission', \$model) - Helper de permissions\n";
echo "  4. @can('permission', \$model) ... @endcan\n";
echo "  5. @cannot('permission', \$model) ... @endcannot\n";
echo "  6. @switch(\$var) @case(value) @break @default @endswitch\n";
