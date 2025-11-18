@auth
    <!-- Menu Search Functionality -->
    <script>
        // Pass menu data from PHP to JavaScript
        window.menuData = @json(session('menu', []));

        document.addEventListener('DOMContentLoaded', function() {
            // Check if elements exist
            const searchInput = document.getElementById('menu_search_input');
            const emptyState = document.getElementById('menu_search_empty');
            const loadingState = document.getElementById('menu_search_loading');
            const noResultsState = document.getElementById('menu_search_no_results');
            const resultsState = document.getElementById('menu_search_results');
            const resultsContainer = document.getElementById('menu_results_container');
            const searchModal = document.getElementById('search_modal');

            if (!searchInput || !emptyState || !loadingState || !noResultsState || !resultsState || !
                resultsContainer || !searchModal) {
                console.error('Menu search: Required elements not found');
                return;
            }

            // Validate menu data
            if (!window.menuData || !Array.isArray(window.menuData)) {
                console.warn('Menu search: No menu data available or invalid format');
                window.menuData = [];
            }

            let searchTimeout;
            let currentIndex = -1;
            let searchResults = [];

            // Flatten menu structure for searching
            function flattenMenus(menus, parentPath = '') {
                let flattened = [];

                if (!Array.isArray(menus)) {
                    return flattened;
                }

                menus.forEach(menu => {
                    if (!menu || typeof menu !== 'object') {
                        return;
                    }

                    const title = menu.title || menu.name || 'Untitled';
                    const currentPath = parentPath ? `${parentPath} > ${title}` : title;

                    // Add current menu if it has a URL
                    if (menu.url) {
                        flattened.push({
                            title: title,
                            url: menu.url,
                            icon: menu.icon || 'ki-filled ki-menu',
                            breadcrumb: currentPath,
                            parentPath: parentPath
                        });
                    }

                    // Add children recursively
                    if (menu.children && Array.isArray(menu.children) && menu.children.length > 0) {
                        flattened = flattened.concat(flattenMenus(menu.children, currentPath));
                    }
                });

                return flattened;
            }

            const flatMenus = flattenMenus(window.menuData);

            // Search function
            function searchMenus(query) {
                if (!query.trim()) {
                    return [];
                }

                const searchTerm = query.toLowerCase();
                return flatMenus.filter(menu => {
                    const title = menu.title || '';
                    const breadcrumb = menu.breadcrumb || '';
                    const url = menu.url || '';

                    return title.toLowerCase().includes(searchTerm) ||
                        breadcrumb.toLowerCase().includes(searchTerm) ||
                        url.toLowerCase().includes(searchTerm);
                });
            }

            // Render search results
            function renderResults(results) {
                resultsContainer.innerHTML = '';

                if (results.length === 0) {
                    showState('no_results');
                    return;
                }

                results.forEach((menu, index) => {
                    if (!menu || !menu.url) {
                        return;
                    }

                    const resultItem = document.createElement('div');
                    resultItem.className =
                        'menu-search-item flex items-center gap-3 px-5 py-3 hover:bg-accent/20 cursor-pointer transition-all duration-200 rounded-lg mx-2 border-2 border-transparent';
                    resultItem.dataset.index = index;
                    resultItem.dataset.url = menu.url;

                    const title = (menu.title || 'Untitled').replace(/[<>&"']/g, function(m) {
                        return {
                            '<': '&lt;',
                            '>': '&gt;',
                            '&': '&amp;',
                            '"': '&quot;',
                            "'": '&#39;'
                        } [m];
                    });

                    const breadcrumb = (menu.breadcrumb || '').replace(/[<>&"']/g, function(m) {
                        return {
                            '<': '&lt;',
                            '>': '&gt;',
                            '&': '&amp;',
                            '"': '&quot;',
                            "'": '&#39;'
                        } [m];
                    });

                    const icon = menu.icon || 'ki-filled ki-menu';

                    resultItem.innerHTML = `
                        <div class="flex-shrink-0">
                            <i class="${icon} text-lg text-muted-foreground"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm text-foreground">${title}</div>
                            <div class="text-xs text-muted-foreground truncate">${breadcrumb}</div>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="ki-filled ki-arrow-right text-sm text-muted-foreground"></i>
                        </div>
                    `;

                    resultItem.addEventListener('click', () => navigateToMenu(menu.url));
                    resultsContainer.appendChild(resultItem);
                });

                showState('results');
                currentIndex = -1;
            }

            // Show different states
            function showState(state) {
                emptyState.classList.add('hidden');
                loadingState.classList.add('hidden');
                noResultsState.classList.add('hidden');
                resultsState.classList.add('hidden');

                switch (state) {
                    case 'empty':
                        emptyState.classList.remove('hidden');
                        break;
                    case 'loading':
                        loadingState.classList.remove('hidden');
                        break;
                    case 'no_results':
                        noResultsState.classList.remove('hidden');
                        break;
                    case 'results':
                        resultsState.classList.remove('hidden');
                        break;
                }
            }

            // Navigate to menu
            function navigateToMenu(url) {
                if (url) {
                    window.location.href = url.startsWith('/') ? url : '/' + url;
                }
            }

            // Handle keyboard navigation
            function handleKeyNavigation(e) {
                const items = resultsContainer.querySelectorAll('.menu-search-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentIndex = Math.min(currentIndex + 1, items.length - 1);
                    updateSelection(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentIndex = Math.max(currentIndex - 1, -1);
                    updateSelection(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (currentIndex >= 0 && items[currentIndex]) {
                        const url = items[currentIndex].dataset.url;
                        navigateToMenu(url);
                    }
                } else if (e.key === 'Escape') {
                    closeModal();
                }
            }

            // Update visual selection
            function updateSelection(items) {
                items.forEach((item, index) => {
                    if (index === currentIndex) {
                        // Add active/selected styling
                        item.classList.add('bg-primary/10', 'border-primary/20', 'border-2');
                        item.classList.remove('border-transparent');
                        // Add subtle scale effect
                        item.style.transform = 'scale(1.02)';
                        item.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                    } else {
                        // Remove active/selected styling
                        item.classList.remove('bg-primary/10', 'border-primary/20', 'border-2');
                        item.classList.add('border-transparent');
                        item.style.transform = 'scale(1)';
                        item.style.boxShadow = 'none';
                    }
                });

                // Scroll to selected item if needed
                if (currentIndex >= 0 && items[currentIndex]) {
                    items[currentIndex].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }

            // Close modal
            function closeModal() {
                searchModal.dispatchEvent(new CustomEvent('kt.modal.hide'));
            }

            // Search input handler with debouncing
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value;

                clearTimeout(searchTimeout);

                if (!query.trim()) {
                    showState('empty');
                    searchResults = [];
                    currentIndex = -1;
                    return;
                }

                showState('loading');

                searchTimeout = setTimeout(() => {
                    searchResults = searchMenus(query);
                    renderResults(searchResults);
                }, 300);
            });

            // Keyboard navigation
            searchInput.addEventListener('keydown', handleKeyNavigation);

            // Reset modal when opened
            searchModal.addEventListener('kt.modal.show', function() {
                searchInput.value = '';
                searchInput.focus();
                showState('empty');
                currentIndex = -1;
                searchResults = [];
            });

            // Clear states when modal is closed
            searchModal.addEventListener('kt.modal.hide', function() {
                searchInput.value = '';
                showState('empty');
                currentIndex = -1;
                searchResults = [];
            });

            // Global keyboard shortcut: Cmd+K (Mac) or Ctrl+K (Windows/Linux)
            document.addEventListener('keydown', function(e) {
                // Check for Cmd+K on Mac or Ctrl+K on Windows/Linux
                if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
                    // Prevent default browser behavior (like opening address bar)
                    e.preventDefault();
                    e.stopPropagation();

                    // Don't open if modal is already open
                    if (searchModal.classList.contains('show') || searchModal.style.display === 'block') {
                        return;
                    }

                    // Try to use KTModal API if available
                    if (window.KTModal) {
                        const modal = KTModal.getInstance(searchModal);
                        if (modal) {
                            modal.show();
                        } else {
                            // Initialize and show modal
                            const newModal = new KTModal(searchModal);
                            newModal.show();
                        }
                    } else {
                        // Fallback: simulate click on the search button
                        const searchButton = document.querySelector(
                            '[data-kt-modal-toggle="#search_modal"]');
                        if (searchButton) {
                            searchButton.click();
                        } else {
                            // Final fallback: manually show modal
                            searchModal.classList.add('show');
                            searchModal.style.display = 'block';
                            document.body.classList.add('modal-open');
                        }
                    }

                    // Focus the search input after a short delay to ensure modal is open
                    setTimeout(() => {
                        if (searchInput) {
                            searchInput.focus();
                        }
                    }, 150);
                }
            });
        });
    </script>
    <!-- End of Scripts -->
@endauth
