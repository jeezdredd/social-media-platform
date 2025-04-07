document.addEventListener('DOMContentLoaded', function() {
    const searchIcon = document.getElementById('searchIcon');
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const searchContainer = document.getElementById('searchContainer');

    // If there's a search value, expand the search bar on page load
    if (searchInput.value.trim() !== '') {
        searchForm.classList.remove('collapsed');
        searchForm.classList.add('expanded');
    }

    searchIcon.addEventListener('click', function() {
        if (searchForm.classList.contains('collapsed')) {
            // Expand
            searchForm.classList.remove('collapsed');
            searchForm.classList.add('expanded');
            setTimeout(() => searchInput.focus(), 300);
        } else {
            // If input has text, submit the form
            if (searchInput.value.trim() !== '') {
                searchForm.submit();
            } else {
                // If input is empty, collapse
                searchForm.classList.remove('expanded');
                searchForm.classList.add('collapsed');
            }
        }
    });

    // Submit form when pressing Enter key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && searchInput.value.trim() !== '') {
            searchForm.submit();
        }
    });

    // Close search when clicking outside
    document.addEventListener('click', function(event) {
        if (!searchContainer.contains(event.target) &&
            searchForm.classList.contains('expanded') &&
            searchInput.value.trim() === '') {
            searchForm.classList.remove('expanded');
            searchForm.classList.add('collapsed');
        }
    });
});