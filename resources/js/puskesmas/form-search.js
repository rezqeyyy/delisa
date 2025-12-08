// resources/js/puskesmas/form-search.js

/**
 * Form-specific search for edit profile and other form pages
 * Mencari dan highlight field/form labels, membantu user menemukan input yang dicari
 */

export class FormSearch {
    constructor(options = {}) {
        this.config = {
            searchInputId: 'dashboardSearch',
            highlightColor: '#fef3c7',
            focusField: true,
            scrollToField: true,
            ...options
        };
        
        this.searchInput = document.getElementById(this.config.searchInputId);
        if (!this.searchInput) return;
        
        this.form = document.querySelector('form');
        this.labels = this.form ? Array.from(this.form.querySelectorAll('label')) : [];
        this.inputs = this.form ? Array.from(this.form.querySelectorAll('input, textarea, select')) : [];
        
        this.init();
    }
    
    init() {
        // Real-time search
        let timeout;
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.performSearch(e.target.value);
            }, 300);
        });
        
        // Clear on escape
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.searchInput.value = '';
                this.clearHighlights();
                this.searchInput.blur();
            }
        });
        
        // Add clear button
        this.addClearButton();
    }
    
    performSearch(searchTerm) {
        this.clearHighlights();
        
        if (!searchTerm || searchTerm.trim() === '') {
            return;
        }
        
        const term = searchTerm.toLowerCase().trim();
        let foundAny = false;
        
        // 1. Search in labels
        this.labels.forEach(label => {
            const labelText = label.textContent.toLowerCase();
            if (labelText.includes(term)) {
                this.highlightElement(label, term);
                foundAny = true;
                
                // Focus corresponding input if exists
                const inputId = label.getAttribute('for');
                if (inputId && this.config.focusField) {
                    const input = document.getElementById(inputId);
                    if (input) {
                        if (this.config.scrollToField) {
                            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        input.focus();
                    }
                }
            }
        });
        
        // 2. Search in input placeholders
        this.inputs.forEach(input => {
            const placeholder = input.getAttribute('placeholder') || '';
            if (placeholder.toLowerCase().includes(term)) {
                this.highlightElement(input, term, true);
                foundAny = true;
            }
        });
        
        // 3. Search in any text on page
        if (!foundAny) {
            this.searchInPageText(term);
        }
    }
    
    highlightElement(element, searchTerm, isPlaceholder = false) {
        if (isPlaceholder) {
            // For placeholders, we can't highlight directly, so highlight the parent label or container
            const parentLabel = element.closest('label') || element.parentElement;
            if (parentLabel) {
                this.highlightText(parentLabel, searchTerm);
            }
        } else {
            this.highlightText(element, searchTerm);
        }
    }
    
    highlightText(element, searchTerm) {
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function(node) {
                    if (node.parentElement.tagName === 'INPUT' || 
                        node.parentElement.tagName === 'BUTTON' ||
                        node.parentElement.tagName === 'TEXTAREA' ||
                        node.parentElement.tagName === 'SELECT' ||
                        node.parentElement.classList.contains('no-highlight')) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );
        
        const nodes = [];
        let node;
        while (node = walker.nextNode()) {
            nodes.push(node);
        }
        
        nodes.forEach(textNode => {
            const text = textNode.textContent;
            if (text.toLowerCase().includes(searchTerm.toLowerCase())) {
                const span = document.createElement('span');
                span.innerHTML = text.replace(
                    new RegExp(`(${this.escapeRegExp(searchTerm)})`, 'gi'),
                    `<mark class="form-highlight px-1 rounded font-medium" style="background-color: ${this.config.highlightColor}">$1</mark>`
                );
                textNode.parentNode.replaceChild(span, textNode);
            }
        });
    }
    
    searchInPageText(searchTerm) {
        // Search in all text content (excluding forms and navigation)
        const contentElements = document.querySelectorAll('main h1, main h2, main h3, main p, main li');
        
        contentElements.forEach(element => {
            if (element.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                this.highlightText(element, searchTerm);
            }
        });
    }
    
    clearHighlights() {
        document.querySelectorAll('.form-highlight').forEach(mark => {
            const parent = mark.parentNode;
            const text = document.createTextNode(mark.textContent);
            parent.replaceChild(text, mark);
            parent.normalize();
        });
    }
    
    escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    addClearButton() {
        const wrapper = this.searchInput.parentNode;
        wrapper.classList.add('relative');
        
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'absolute inset-y-0 right-3 flex items-center hidden';
        clearBtn.innerHTML = `
            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600 transition-colors" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" 
                      stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        `;
        clearBtn.title = 'Clear search';
        
        clearBtn.addEventListener('click', () => {
            this.searchInput.value = '';
            this.clearHighlights();
            this.searchInput.focus();
            clearBtn.classList.add('hidden');
        });
        
        wrapper.appendChild(clearBtn);
        
        this.searchInput.addEventListener('input', () => {
            clearBtn.classList.toggle('hidden', this.searchInput.value.length === 0);
        });
    }
}

// Auto-initialize on form pages
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('dashboardSearch');
    const hasForm = document.querySelector('form');
    
    // Jika ada search input dan ada form di halaman
    if (searchInput && hasForm) {
        new FormSearch();
    }
});