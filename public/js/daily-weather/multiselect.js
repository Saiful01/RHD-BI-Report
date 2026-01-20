/**
 * Searchable Multiselect Component
 * Usage: createStationMultiselect(containerId, stationData, options)
 */

class Multiselect {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Multiselect container #${containerId} not found`);
            return;
        }

        this.options = {
            placeholder: options.placeholder || 'Select items...',
            searchPlaceholder: options.searchPlaceholder || 'Search...',
            noResultsText: options.noResultsText || 'No results found',
            selectAllText: options.selectAllText || 'Select All',
            deselectAllText: options.deselectAllText || 'Deselect All',
            maxTagsVisible: options.maxTagsVisible || 3,
            items: options.items || [],
            selectedValues: options.selectedValues || [],
            onChange: options.onChange || null,
            selectAllByDefault: options.selectAllByDefault || false,
        };

        this.selectedItems = new Set(this.options.selectedValues.map(v => String(v)));
        this.isOpen = false;

        if (this.options.selectAllByDefault && this.options.items.length > 0) {
            this.options.items.forEach(item => this.selectedItems.add(String(item.value)));
        }

        this.render();
        this.bindEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="multiselect-trigger" tabindex="0">
                <div class="multiselect-selected">
                    ${this.renderSelectedTags()}
                </div>
                <div class="multiselect-arrow">
                    <i class="ri-arrow-down-s-line"></i>
                </div>
            </div>
            <div class="multiselect-dropdown">
                <div class="multiselect-search">
                    <i class="ri-search-line multiselect-search-icon"></i>
                    <input type="text" class="multiselect-search-input" placeholder="${this.options.searchPlaceholder}">
                </div>
                <div class="multiselect-actions">
                    <button type="button" class="multiselect-action-btn select-all">
                        <i class="ri-checkbox-multiple-line"></i> ${this.options.selectAllText}
                    </button>
                    <button type="button" class="multiselect-action-btn deselect-all">
                        <i class="ri-checkbox-blank-line"></i> ${this.options.deselectAllText}
                    </button>
                </div>
                <div class="multiselect-options">
                    ${this.renderOptions()}
                </div>
            </div>
        `;

        this.trigger = this.container.querySelector('.multiselect-trigger');
        this.dropdown = this.container.querySelector('.multiselect-dropdown');
        this.searchInput = this.container.querySelector('.multiselect-search-input');
        this.optionsContainer = this.container.querySelector('.multiselect-options');
        this.selectedContainer = this.container.querySelector('.multiselect-selected');
    }

    renderSelectedTags() {
        const selected = Array.from(this.selectedItems);
        const items = this.options.items;

        if (selected.length === 0) {
            return `<span class="multiselect-placeholder">${this.options.placeholder}</span>`;
        }

        if (selected.length === items.length && items.length > 0) {
            return `
                <span class="multiselect-selected-count">
                    <i class="ri-checkbox-multiple-fill"></i>
                    All ${items.length} stations selected
                </span>
            `;
        }

        if (selected.length > this.options.maxTagsVisible) {
            const visibleItems = selected.slice(0, this.options.maxTagsVisible);
            const remaining = selected.length - this.options.maxTagsVisible;

            let html = visibleItems.map(value => {
                const item = items.find(i => String(i.value) === value);
                return item ? `
                    <span class="multiselect-tag" data-value="${item.value}">
                        <span>${item.label}</span>
                        <span class="multiselect-tag-remove" data-value="${item.value}">
                            <i class="ri-close-line"></i>
                        </span>
                    </span>
                ` : '';
            }).join('');

            html += `<span class="multiselect-count">+${remaining} more</span>`;
            return html;
        }

        return selected.map(value => {
            const item = items.find(i => String(i.value) === value);
            return item ? `
                <span class="multiselect-tag" data-value="${item.value}">
                    <span>${item.label}</span>
                    <span class="multiselect-tag-remove" data-value="${item.value}">
                        <i class="ri-close-line"></i>
                    </span>
                </span>
            ` : '';
        }).join('');
    }

    renderOptions() {
        if (this.options.items.length === 0) {
            return `
                <div class="multiselect-no-results">
                    <i class="ri-inbox-line"></i>
                    No items available
                </div>
            `;
        }

        return this.options.items.map(item => {
            const isSelected = this.selectedItems.has(String(item.value));
            return `
                <div class="multiselect-option ${isSelected ? 'selected' : ''}" data-value="${item.value}" data-label="${item.label.toLowerCase()}">
                    <div class="multiselect-checkbox">
                        ${isSelected ? '<i class="ri-check-line"></i>' : ''}
                    </div>
                    <i class="ri-map-pin-2-line multiselect-option-icon"></i>
                    <span class="multiselect-option-label">${item.label}</span>
                </div>
            `;
        }).join('');
    }

    bindEvents() {
        // Toggle dropdown
        this.trigger.addEventListener('click', (e) => {
            if (e.target.closest('.multiselect-tag-remove')) {
                return;
            }
            this.toggle();
        });

        // Keyboard navigation
        this.trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggle();
            } else if (e.key === 'Escape') {
                this.close();
            }
        });

        // Search
        this.searchInput.addEventListener('input', (e) => {
            this.filterOptions(e.target.value);
        });

        // Prevent dropdown close on search input click
        this.searchInput.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Prevent dropdown close on dropdown click
        this.dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Select all
        this.container.querySelector('.select-all').addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectAll();
        });

        // Deselect all
        this.container.querySelector('.deselect-all').addEventListener('click', (e) => {
            e.stopPropagation();
            this.deselectAll();
        });

        // Option click
        this.optionsContainer.addEventListener('click', (e) => {
            const option = e.target.closest('.multiselect-option');
            if (option) {
                this.toggleOption(option.dataset.value);
            }
        });

        // Tag remove
        this.selectedContainer.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.multiselect-tag-remove');
            if (removeBtn) {
                e.stopPropagation();
                this.toggleOption(removeBtn.dataset.value);
            }
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            try {
                if (this.container && this.dropdown &&
                    !this.container.contains(e.target) &&
                    !this.dropdown.contains(e.target)) {
                    this.close();
                }
            } catch (err) {
                console.error('Multiselect click handler error:', err);
            }
        });

        // Close on escape key anywhere
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });

        // Reposition on scroll/resize
        window.addEventListener('scroll', () => {
            if (this.isOpen) {
                this.positionDropdown();
            }
        }, true);

        window.addEventListener('resize', () => {
            if (this.isOpen) {
                this.positionDropdown();
            }
        });
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    positionDropdown() {
        const rect = this.trigger.getBoundingClientRect();
        const dropdownHeight = 400; // max-height from CSS
        const viewportHeight = window.innerHeight;
        const spaceBelow = viewportHeight - rect.bottom;
        const spaceAbove = rect.top;

        // Determine if dropdown should appear above or below
        let top;
        if (spaceBelow >= dropdownHeight || spaceBelow >= spaceAbove) {
            // Show below
            top = rect.bottom + 4;
        } else {
            // Show above
            top = rect.top - Math.min(dropdownHeight, spaceAbove - 10);
        }

        this.dropdown.style.position = 'fixed';
        this.dropdown.style.top = `${top}px`;
        this.dropdown.style.left = `${rect.left}px`;
        this.dropdown.style.width = `${rect.width}px`;
        this.dropdown.style.minWidth = '280px';
    }

    open() {
        // Close any other open multiselects
        document.querySelectorAll('.multiselect-dropdown.show').forEach(dd => {
            dd.classList.remove('show');
        });
        document.querySelectorAll('.multiselect-trigger.active').forEach(tr => {
            tr.classList.remove('active');
        });

        this.isOpen = true;
        this.trigger.classList.add('active');
        this.positionDropdown();
        this.dropdown.classList.add('show');
        this.searchInput.value = '';
        this.filterOptions('');
        setTimeout(() => this.searchInput.focus(), 50);
    }

    close() {
        this.isOpen = false;
        this.trigger.classList.remove('active');
        this.dropdown.classList.remove('show');
    }

    filterOptions(query) {
        const options = this.optionsContainer.querySelectorAll('.multiselect-option');
        const normalizedQuery = query.toLowerCase().trim();
        let visibleCount = 0;

        options.forEach(option => {
            const label = option.dataset.label;
            if (normalizedQuery === '' || label.includes(normalizedQuery)) {
                option.classList.remove('hidden');
                visibleCount++;
            } else {
                option.classList.add('hidden');
            }
        });

        // Show/hide no results message
        let noResults = this.optionsContainer.querySelector('.multiselect-no-results');
        if (visibleCount === 0 && this.options.items.length > 0) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.className = 'multiselect-no-results';
                noResults.innerHTML = `<i class="ri-search-line"></i>${this.options.noResultsText}`;
                this.optionsContainer.appendChild(noResults);
            }
            noResults.style.display = 'block';
        } else if (noResults) {
            noResults.style.display = 'none';
        }
    }

    toggleOption(value) {
        value = String(value);
        if (this.selectedItems.has(value)) {
            this.selectedItems.delete(value);
        } else {
            this.selectedItems.add(value);
        }
        this.updateUI();
        this.triggerChange();
    }

    selectAll() {
        this.options.items.forEach(item => {
            this.selectedItems.add(String(item.value));
        });
        this.updateUI();
        this.triggerChange();
    }

    deselectAll() {
        this.selectedItems.clear();
        this.updateUI();
        this.triggerChange();
    }

    updateUI() {
        // Update selected tags
        this.selectedContainer.innerHTML = this.renderSelectedTags();

        // Update options
        const options = this.optionsContainer.querySelectorAll('.multiselect-option');
        options.forEach(option => {
            const isSelected = this.selectedItems.has(String(option.dataset.value));
            option.classList.toggle('selected', isSelected);
            option.querySelector('.multiselect-checkbox').innerHTML = isSelected ? '<i class="ri-check-line"></i>' : '';
        });
    }

    triggerChange() {
        if (this.options.onChange) {
            this.options.onChange(this.getSelectedValues());
        }
    }

    getSelectedValues() {
        return Array.from(this.selectedItems);
    }

    getSelectedItems() {
        return this.options.items.filter(item => this.selectedItems.has(String(item.value)));
    }

    setSelected(values) {
        this.selectedItems = new Set(values.map(v => String(v)));
        this.updateUI();
    }
}

// Helper function to create multiselect from existing data
function createStationMultiselect(containerId, stations, options = {}) {
    const items = [];

    // Handle different station data formats
    if (Array.isArray(stations)) {
        stations.forEach(station => {
            if (typeof station === 'object') {
                items.push({
                    value: station.id || station.value,
                    label: station.name || station.label || station.station_name
                });
            }
        });
    } else if (typeof stations === 'object') {
        // Handle {id: name} format
        Object.entries(stations).forEach(([id, name]) => {
            items.push({ value: id, label: name });
        });
    }

    return new Multiselect(containerId, {
        items: items,
        placeholder: 'Select stations...',
        searchPlaceholder: 'Search stations...',
        selectAllByDefault: options.selectAllByDefault !== false,
        onChange: options.onChange || null,
        ...options
    });
}
