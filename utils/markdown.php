<?php
function parseMarkdown($text) {
    // Convert bold: **text** to <strong>text</strong>
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);

    // Convert links: [text](url) to <a> with external link warning
    $text = preg_replace('/\[(.*?)\]\((https?:\/\/[^\s\)]+)\)/s',
        '<a href="javascript:void(0)" class="external-link" data-url="$2">$1</a>', $text);

    // Convert list items
    $text = preg_replace('/^• (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/((?:<li>.*?<\/li>\s*)+)/', '<ul>$1</ul>', $text);

    return $text;
}
?>