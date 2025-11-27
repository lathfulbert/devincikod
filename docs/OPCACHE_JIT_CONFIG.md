# PHP 8.3 Performance Configuration

## OPcache Configuration (Production)

Add to your `php.ini` or create `conf.d/opcache.ini`:

```ini
[opcache]
; Enable OPcache
opcache.enable=1
opcache.enable_cli=1

; Memory Settings
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000

; Validation
opcache.revalidate_freq=2
opcache.validate_timestamps=1  ; Set to 0 in production for max performance

; Optimization Level
opcache.optimization_level=0x7FFEBFFF

; File Cache (optional but recommended)
opcache.file_cache=/tmp/opcache
opcache.file_cache_only=0

; JIT Configuration (PHP 8.0+)
opcache.jit_buffer_size=256M
opcache.jit=tracing  ; or "1255" for maximum performance

; Preloading (optional)
; opcache.preload=/path/to/preload.php
```

## JIT Modes

```ini
; Disabled
opcache.jit=off

; Tracing JIT (recommended for most apps)
opcache.jit=tracing

; Function JIT (faster compilation, less optimization)
opcache.jit=function

; Numeric mode (fine-tuning)
; opcache.jit=1255
; Format: CRTO
; C - CPU-specific optimization (0-4)
; R - JIT level (0-5)
; T - Trigger (0-5)
; O - Optimization level (0-5)
```

## Verification

```php
<?php
// Check OPcache status
var_dump(opcache_get_status());

// Check JIT
var_dump(opcache_get_status()['jit']);

// Preload script example
opcache_compile_file(__DIR__ . '/important-file.php');
```

## Recommendations

### Development

- `opcache.validate_timestamps=1` (auto-reload on file changes)
- `opcache.revalidate_freq=0` (check every request)
- `opcache.jit=tracing`

### Production

- `opcache.validate_timestamps=0` (no timestamp checks = faster)
- `opcache.jit=1255` (maximum optimization)
- Enable preloading for core files

## Performance Gains

With OPcache + JIT on PHP 8.3:

- **OPcache alone**: 2-3x faster
- **OPcache + JIT**: 3-5x faster for compute-heavy code
- **Memory**: Reduced by ~30-50%
