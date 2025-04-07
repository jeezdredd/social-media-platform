document.addEventListener('DOMContentLoaded', function() {
    let currentUrl = '';
    const modal = document.getElementById('externalLinkModal');
    const urlDisplay = document.querySelector('.external-url-display');
    const continueBtn = document.getElementById('openExternalLink');
    const cancelBtn = document.getElementById('cancelExternalLink');

    document.addEventListener('click', function(e) {
        const externalLink = e.target.closest('.external-link');
        if (externalLink) {
            currentUrl = externalLink.getAttribute('data-url');
            urlDisplay.textContent = currentUrl;

            modal.style.display = 'block';
            e.preventDefault();
        }
    });

    continueBtn.addEventListener('click', function() {
        if (currentUrl) {
            window.open(currentUrl, '_blank');
        }
        modal.style.display = 'none';
    });

    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});