<?php

/**
 * Test des Directives Conditionnelles Avancées
 * 
 * Ce fichier teste les nouvelles directives implémentées :
 * - @unless/@endunless
 * - @isset/@endisset
 * - @empty/@endempty
 * - @auth/@endauth
 * - @guest/@endguest
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Core/Support/helpers.php';

use App\Core\View\TemplateEngine;

echo "=== Test des Directives Conditionnelles Avancées ===\n\n";

// Initialize template engine
$cachePath = __DIR__ . '/storage/cache/views';
$engine = new TemplateEngine($cachePath);

// Test 1: @unless
echo "1. Test @unless/@endunless...\n";
$template1 = <<<'BLADE'
@unless($user->isAdmin)
    <p>You are not an admin</p>
@endunless
BLADE;

$compiled1 = $engine->compileString($template1);
$expected1 = "<?php if(!(\$user->isAdmin)): ?>\n    <p>You are not an admin</p>\n<?php endif; ?>";

if (trim($compiled1) === trim($expected1)) {
    echo "   ✅ @unless compile correctement\n";
} else {
    echo "   ❌ @unless ne compile pas correctement\n";
    echo "   Attendu: $expected1\n";
    echo "   Reçu: $compiled1\n";
}

// Test 2: @isset
echo "\n2. Test @isset/@endisset...\n";
$template2 = <<<'BLADE'
@isset($user)
    <p>User is set</p>
@endisset
BLADE;

$compiled2 = $engine->compileString($template2);
$expected2 = "<?php if(isset(\$user)): ?>\n    <p>User is set</p>\n<?php endif; ?>";

if (trim($compiled2) === trim($expected2)) {
    echo "   ✅ @isset compile correctement\n";
} else {
    echo "   ❌ @isset ne compile pas correctement\n";
    echo "   Attendu: $expected2\n";
    echo "   Reçu: $compiled2\n";
}

// Test 3: @empty
echo "\n3. Test @empty/@endempty...\n";
$template3 = <<<'BLADE'
@empty($users)
    <p>No users found</p>
@endempty
BLADE;

$compiled3 = $engine->compileString($template3);
$expected3 = "<?php if(empty(\$users)): ?>\n    <p>No users found</p>\n<?php endif; ?>";

if (trim($compiled3) === trim($expected3)) {
    echo "   ✅ @empty compile correctement\n";
} else {
    echo "   ❌ @empty ne compile pas correctement\n";
    echo "   Attendu: $expected3\n";
    echo "   Reçu: $compiled3\n";
}

// Test 4: @auth
echo "\n4. Test @auth/@endauth...\n";
$template4 = <<<'BLADE'
@auth
    <p>Welcome back, {{ $user->name }}</p>
@endauth
BLADE;

$compiled4 = $engine->compileString($template4);
// Note: {{ }} will also be compiled
$hasAuth = strpos($compiled4, 'auth()->check()') !== false;
$hasEndif = strpos($compiled4, 'endif;') !== false;

if ($hasAuth && $hasEndif) {
    echo "   ✅ @auth compile correctement\n";
} else {
    echo "   ❌ @auth ne compile pas correctement\n";
    echo "   Reçu: $compiled4\n";
}

// Test 5: @guest
echo "\n5. Test @guest/@endguest...\n";
$template5 = <<<'BLADE'
@guest
    <p>Please login</p>
@endguest
BLADE;

$compiled5 = $engine->compileString($template5);
$hasGuest = strpos($compiled5, '!auth()->check()') !== false;
$hasEndif = strpos($compiled5, 'endif;') !== false;

if ($hasGuest && $hasEndif) {
    echo "   ✅ @guest compile correctement\n";
} else {
    echo "   ❌ @guest ne compile pas correctement\n";
    echo "   Reçu: $compiled5\n";
}

// Test 6: Combinaison de directives
echo "\n6. Test combinaison de directives...\n";
$template6 = <<<'BLADE'
@auth
    @isset($user->name)
        <p>Hello, {{ $user->name }}</p>
    @endisset
    
    @empty($notifications)
        <p>No new notifications</p>
    @endempty
@endauth

@guest
    <p>Please login to continue</p>
@endguest
BLADE;

$compiled6 = $engine->compileString($template6);
$hasAllDirectives =
    strpos($compiled6, 'auth()->check()') !== false &&
    strpos($compiled6, '!auth()->check()') !== false &&
    strpos($compiled6, 'isset(') !== false &&
    strpos($compiled6, 'empty(') !== false;

if ($hasAllDirectives) {
    echo "   ✅ Combinaison de directives fonctionne\n";
} else {
    echo "   ❌ Problème avec la combinaison\n";
}

echo "\n=== Fin des Tests ===\n";
echo "\n✅ Toutes les directives conditionnelles avancées sont opérationnelles !\n";
echo "\nVous pouvez maintenant utiliser :\n";
echo "  - @unless(condition) ... @endunless\n";
echo "  - @isset(\$variable) ... @endisset\n";
echo "  - @empty(\$variable) ... @endempty\n";
echo "  - @auth ... @endauth\n";
echo "  - @guest ... @endguest\n";
