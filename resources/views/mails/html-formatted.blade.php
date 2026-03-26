@php
    $formatted = trim($html);

    // Put each tag on its own line
    $formatted = preg_replace('/>\s*</', ">\n<", $formatted);

    // Indent based on nesting depth
    $lines = explode("\n", $formatted);
    $indent = 0;
    $result = [];
    $selfClosingPattern = '/^<(?:meta|link|br|hr|img|input|source|col|area|base|embed|param|track|wbr)\b/i';
    $closingPattern = '/^<\//';
    $openingPattern = '/^<[a-zA-Z]/';

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '') {
            continue;
        }

        if (preg_match($closingPattern, $line)) {
            $indent = max(0, $indent - 1);
        }

        $result[] = str_repeat('  ', $indent) . $line;

        if (preg_match($openingPattern, $line)
            && !preg_match($selfClosingPattern, $line)
            && !preg_match($closingPattern, $line)
            && !str_contains($line, '/>')
            && !preg_match('/<\/[a-zA-Z][^>]*>\s*$/', $line)
        ) {
            $indent++;
        }
    }

    $formatted = implode("\n", $result);
@endphp

<div class="prose prose-sm sm:prose lg:prose-lg xl:prose-2xl max-w-full overflow-x-auto">
    <pre class="whitespace-pre-wrap break-words"><code class="language-html">{{ $formatted }}</code></pre>
</div>
