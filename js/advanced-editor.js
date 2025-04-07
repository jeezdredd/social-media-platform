document.addEventListener('DOMContentLoaded', function() {
    // Initialize all textarea editors on the page
    initAdvancedEditors();

    // Initialize all comment input editors
    initCommentEditors();
});

function initAdvancedEditors() {
    const textareas = document.querySelectorAll('.post-input textarea');

    textareas.forEach(textarea => {
        if (!textarea.parentNode.querySelector('.editor-toolbar')) {
            createToolbar(textarea, true);
        }
    });
}

function initCommentEditors() {
    const commentInputs = document.querySelectorAll('.comment-form input[type="text"]');

    commentInputs.forEach(input => {
        if (!input.parentNode.querySelector('.editor-toolbar')) {
            createToolbar(input, false);
        }
    });
}

function createToolbar(element, isTextarea) {
    // Create toolbar container
    const toolbar = document.createElement('div');
    toolbar.className = 'editor-toolbar';

    // Add emoji button
    const emojiButton = document.createElement('button');
    emojiButton.type = 'button';
    emojiButton.innerHTML = '😊';
    emojiButton.title = 'Insert emoji';
    emojiButton.className = 'toolbar-btn';
    emojiButton.addEventListener('click', function(e) {
        e.preventDefault();
        showEmojiPicker(element);
    });

    // Add bold button
    const boldButton = document.createElement('button');
    boldButton.type = 'button';
    boldButton.innerHTML = 'B';
    boldButton.title = 'Bold text';
    boldButton.className = 'toolbar-btn bold-btn';
    boldButton.addEventListener('click', function(e) {
        e.preventDefault();
        insertFormatting(element, '**', '**');
    });

    // Add list button
    const listButton = document.createElement('button');
    listButton.type = 'button';
    listButton.innerHTML = '•';
    listButton.title = 'Create list';
    listButton.className = 'toolbar-btn list-btn';
    listButton.addEventListener('click', function(e) {
        e.preventDefault();
        insertList(element);
    });

    // Add link button
    const linkButton = document.createElement('button');
    linkButton.type = 'button';
    linkButton.innerHTML = '🔗';
    linkButton.title = 'Insert link';
    linkButton.className = 'toolbar-btn link-btn';
    linkButton.addEventListener('click', function(e) {
        e.preventDefault();
        insertLink(element);
    });

    // Add buttons to toolbar
    toolbar.appendChild(emojiButton);

    // Add formatting buttons to both text areas and comment inputs
    toolbar.appendChild(boldButton);
    toolbar.appendChild(listButton);
    toolbar.appendChild(linkButton);

    // Add toolbar before the textarea/input
    element.parentNode.insertBefore(toolbar, element);
}

function showEmojiPicker(element) {
    // Create emoji picker if it doesn't exist
    if (!document.getElementById('emoji-picker')) {
        const emojiPicker = document.createElement('div');
        emojiPicker.id = 'emoji-picker';
        emojiPicker.className = 'emoji-picker';

        // Common emojis
        const commonEmojis = ['😊', '😂', '❤️', '👍', '🎉', '🔥', '😎', '🤔', '😢', '😡',
            '👏', '🙏', '🥰', '😍', '🤩', '😴', '🤯', '🤣', '😇', '🤪'];

        commonEmojis.forEach(emoji => {
            const emojiBtn = document.createElement('span');
            emojiBtn.className = 'emoji';
            emojiBtn.textContent = emoji;
            emojiBtn.addEventListener('click', function() {
                insertAtCursor(element, emoji);
                emojiPicker.style.display = 'none';
            });
            emojiPicker.appendChild(emojiBtn);
        });

        document.body.appendChild(emojiPicker);

        // Close emoji picker when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#emoji-picker') && e.target.className !== 'toolbar-btn') {
                emojiPicker.style.display = 'none';
            }
        });
    }

    const emojiPicker = document.getElementById('emoji-picker');

    // Position the emoji picker near the button
    const rect = element.getBoundingClientRect();
    emojiPicker.style.top = `${rect.top + window.scrollY - emojiPicker.offsetHeight - 5}px`;
    emojiPicker.style.left = `${rect.left + window.scrollX}px`;

    // Toggle visibility
    emojiPicker.style.display = emojiPicker.style.display === 'block' ? 'none' : 'block';
}

function insertAtCursor(element, text) {
    if (element.selectionStart || element.selectionStart === 0) {
        const startPos = element.selectionStart;
        const endPos = element.selectionEnd;
        element.value = element.value.substring(0, startPos)
            + text
            + element.value.substring(endPos, element.value.length);
        element.selectionStart = startPos + text.length;
        element.selectionEnd = startPos + text.length;
    } else {
        element.value += text;
    }
    element.focus();
}

function insertFormatting(element, prefix, suffix) {
    if (element.selectionStart || element.selectionStart === 0) {
        const startPos = element.selectionStart;
        const endPos = element.selectionEnd;
        const selectedText = element.value.substring(startPos, endPos);

        element.value = element.value.substring(0, startPos)
            + prefix + selectedText + suffix
            + element.value.substring(endPos, element.value.length);

        element.selectionStart = startPos + prefix.length;
        element.selectionEnd = startPos + prefix.length + selectedText.length;
    } else {
        element.value += prefix + suffix;
        element.selectionStart = element.value.length - suffix.length;
        element.selectionEnd = element.selectionStart;
    }
    element.focus();
}

function insertList(element) {
    const listItem = '\n• ';
    insertAtCursor(element, listItem);
}

function insertLink(element) {
    const url = prompt('Enter URL:', 'https://');
    if (url) {
        if (element.selectionStart || element.selectionStart === 0) {
            const startPos = element.selectionStart;
            const endPos = element.selectionEnd;
            const selectedText = element.value.substring(startPos, endPos);
            const linkText = selectedText || 'link text';

            const markdownLink = `[${linkText}](${url})`;

            element.value = element.value.substring(0, startPos)
                + markdownLink
                + element.value.substring(endPos, element.value.length);

            element.selectionStart = startPos;
            element.selectionEnd = startPos + markdownLink.length;
        } else {
            insertAtCursor(element, `[link text](${url})`);
        }
    }
}

function parseMarkdownClient(text) {
    if (!text) return '';

    // Bold: **text** to <strong>text</strong>
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

    // Links: [text](url) to <a>
    text = text.replace(/\[(.*?)\]\((https?:\/\/[^\s\)]+)\)/g,
        '<a href="javascript:void(0)" class="external-link" data-url="$2">$1</a>');

    // Lists: • item to <li>
    text = text.replace(/^• (.*?)$/gm, '<li>$1</li>');
    text = text.replace(/((?:<li>.*?<\/li>\s*)+)/, '<ul>$1</ul>');

    return text;
}