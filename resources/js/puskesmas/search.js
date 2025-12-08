// resources/js/puskesmas/search.js

/**
 * UNIVERSAL SEARCH FOR PUSKESMAS APPLICATION
 * 
 * Dua mode search:
 * 1. PuskesmasSearch - Search di tabel saja (untuk halaman lain)
 * 2. DashboardSearch - Search seluruh dashboard (card + tabel)
 */

// ============================================================================
// 1. PUSKESMAS SEARCH (Untuk halaman tabel biasa)
// ============================================================================

export class PuskesmasSearch {
    constructor(options = {}) {
        this.config = {
            searchInputId: 'dashboardSearch',
            tableBodyId: 'dataTableBody',
            searchColumns: [],
            excludeColumns: [],
            realTime: true,
            minChars: 1,
            highlightMatches: true,
            showClearButton: true,
            noResultsText: 'Tidak ada data yang cocok dengan pencarian Anda.',
            ...options
        };
        
        this.searchInput = document.getElementById(this.config.searchInputId);
        this.tableBody = document.getElementById(this.config.tableBodyId);
        
        if (!this.searchInput || !this.tableBody) {
            console.warn('Search elements not found:', {
                input: this.config.searchInputId,
                table: this.config.tableBodyId
            });
            return;
        }
        
        this.originalRows = Array.from(this.tableBody.querySelectorAll('tr'));
        this.init();
    }
    
    init() {
        this.createNoResultsRow();
        
        if (this.config.realTime) {
            this.setupRealTimeSearch();
        } else {
            this.setupButtonSearch();
        }
        
        if (this.config.showClearButton) {
            this.addClearButton();
        }
        
        this.setupKeyboardShortcuts();
    }
    
    normalizeText(text) {
        return text
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, '')
            .trim();
    }
    
    rowMatchesSearch(row, searchTerm) {
        const normalizedSearchTerm = this.normalizeText(searchTerm);
        const cells = row.querySelectorAll('td');
        
        for (let i = 0; i < cells.length; i++) {
            if (this.config.excludeColumns.includes(i)) continue;
            if (this.config.searchColumns.length > 0 && !this.config.searchColumns.includes(i)) continue;
            
            const cellText = cells[i].textContent || cells[i].innerText;
            const normalizedCellText = this.normalizeText(cellText);
            
            if (normalizedCellText.includes(normalizedSearchTerm)) {
                return true;
            }
        }
        
        return false;
    }
    
    highlightCellText(cell, searchTerm) {
        if (!this.config.highlightMatches || !searchTerm) return;
        
        const originalText = cell.textContent;
        const normalizedText = this.normalizeText(originalText);
        const normalizedSearchTerm = this.normalizeText(searchTerm);
        
        if (!normalizedText.includes(normalizedSearchTerm)) return;
        
        const regex = new RegExp(`(${this.escapeRegExp(searchTerm)})`, 'gi');
        cell.innerHTML = originalText.replace(
            regex, 
            '<mark class="bg-yellow-200 px-0.5 rounded search-highlight">$1</mark>'
        );
    }
    
    removeHighlights(cell) {
        const marks = cell.querySelectorAll('.search-highlight');
        marks.forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize();
        });
    }
    
    escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    performSearch(searchTerm = null) {
        const term = searchTerm || this.searchInput.value.trim();
        
        if (term.length < this.config.minChars) {
            this.resetTable();
            return;
        }
        
        let hasMatches = false;
        
        this.originalRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => this.removeHighlights(cell));
            
            if (this.rowMatchesSearch(row, term)) {
                row.style.display = '';
                hasMatches = true;
                
                cells.forEach((cell, index) => {
                    if (!this.config.excludeColumns.includes(index)) {
                        this.highlightCellText(cell, term);
                    }
                });
            } else {
                row.style.display = 'none';
            }
        });
        
        if (hasMatches) {
            this.hideNoResults();
        } else {
            this.showNoResults();
        }
    }
    
    resetTable() {
        this.originalRows.forEach(row => {
            row.style.display = '';
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => this.removeHighlights(cell));
        });
        this.hideNoResults();
    }
    
    createNoResultsRow() {
        if (document.getElementById('noResultsRow')) {
            this.noResultsRow = document.getElementById('noResultsRow');
            return;
        }
        
        const tr = document.createElement('tr');
        tr.id = 'noResultsRow';
        tr.className = 'hidden';
        
        const td = document.createElement('td');
        td.colSpan = 100;
        td.className = 'px-3 py-6 text-center text-[#7C7C7C]';
        td.textContent = this.config.noResultsText;
        
        tr.appendChild(td);
        this.tableBody.appendChild(tr);
        this.noResultsRow = tr;
    }
    
    showNoResults() {
        if (this.noResultsRow) this.noResultsRow.classList.remove('hidden');
    }
    
    hideNoResults() {
        if (this.noResultsRow) this.noResultsRow.classList.add('hidden');
    }
    
    setupRealTimeSearch() {
        let timeout;
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.performSearch(e.target.value);
            }, 300);
        });
    }
    
    setupButtonSearch() {
        const searchButton = document.getElementById(`${this.config.searchInputId}Btn`) ||
                           this.searchInput.nextElementSibling;
        
        if (searchButton) {
            searchButton.addEventListener('click', () => this.performSearch());
        }
        
        this.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.performSearch();
        });
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
            this.resetTable();
            this.searchInput.focus();
            clearBtn.classList.add('hidden');
        });
        
        wrapper.appendChild(clearBtn);
        
        this.searchInput.addEventListener('input', () => {
            clearBtn.classList.toggle('hidden', this.searchInput.value.length === 0);
        });
    }
    
    setupKeyboardShortcuts() {
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.searchInput.value = '';
                this.resetTable();
                this.searchInput.blur();
            }
        });
    }
    
    search(term) {
        this.searchInput.value = term;
        this.performSearch(term);
    }
    
    clear() {
        this.searchInput.value = '';
        this.resetTable();
    }
    
    destroy() {
        this.searchInput.removeEventListener('input', this.performSearch);
        this.searchInput.removeEventListener('keydown', this.handleKeydown);
        this.resetTable();
    }
}

// ============================================================================
// 2. DASHBOARD SEARCH (Khusus halaman dashboard - filter semua card)
// ============================================================================

export class DashboardSearch {
    constructor() {
        this.searchInput = document.getElementById('dashboardSearch');
        if (!this.searchInput) return;
        
        this.components = this.gatherDashboardComponents();
        this.originalState = this.saveOriginalState();
        
        this.init();
    }
    
    gatherDashboardComponents() {
        const components = [];
        
        // 1. Cards di dashboard
        document.querySelectorAll('section > div.bg-white.rounded-2xl').forEach((card, index) => {
            components.push({
                type: 'card',
                element: card,
                id: `card-${index}`,
                searchableText: this.getCardSearchableText(card),
                category: this.getCardCategory(card)
            });
        });
        
        // 2. Table rows
        const tableBody = document.querySelector('table tbody');
        if (tableBody) {
            tableBody.querySelectorAll('tr').forEach((row, index) => {
                if (row.querySelector('td')) {
                    components.push({
                        type: 'table-row',
                        element: row,
                        id: `row-${index}`,
                        searchableText: row.textContent.toLowerCase(),
                        data: this.extractRowData(row)
                    });
                }
            });
        }
        
        // 3. Table section
        const tableSection = document.querySelector('section:has(table)');
        if (tableSection) {
            components.push({
                type: 'section',
                element: tableSection,
                id: 'table-section',
                searchableText: tableSection.textContent.toLowerCase(),
                title: 'Data Pasien Pre Eklampsia'
            });
        }
        
        return components;
    }
    
    getCardSearchableText(card) {
        const clone = card.cloneNode(true);
        clone.querySelectorAll('button, a, img, svg, .action').forEach(el => el.remove());
        return clone.textContent.toLowerCase().trim();
    }
    
    getCardCategory(card) {
        const text = card.textContent.toLowerCase();
        
        if (text.includes('daerah asal') || text.includes('depok')) return 'daerah';
        if (text.includes('resiko') || text.includes('preeklampsia')) return 'resiko';
        if (text.includes('pasien hadir') || text.includes('tidak hadir')) return 'kehadiran';
        if (text.includes('pasien nifas') || text.includes('kfi')) return 'nifas';
        if (text.includes('pemantauan') || text.includes('sehat') || text.includes('dirujuk') || text.includes('meninggal')) return 'pemantauan';
        
        return 'other';
    }
    
    extractRowData(row) {
        const cells = row.querySelectorAll('td');
        return {
            name: cells[1]?.textContent?.toLowerCase() || '',
            nik: cells[2]?.textContent?.toLowerCase() || '',
            birthDate: cells[3]?.textContent?.toLowerCase() || '',
            address: cells[4]?.textContent?.toLowerCase() || '',
            phone: cells[5]?.textContent?.toLowerCase() || '',
            conclusion: cells[6]?.textContent?.toLowerCase() || ''
        };
    }
    
    saveOriginalState() {
        const state = {};
        this.components.forEach(comp => {
            state[comp.id] = {
                display: comp.element.style.display || 'block',
                html: comp.element.innerHTML
            };
        });
        return state;
    }
    
    normalizeText(text) {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, '')
            .trim();
    }
    
    componentMatches(component, searchTerm) {
        const normalizedSearch = this.normalizeText(searchTerm);
        const normalizedText = this.normalizeText(component.searchableText);
        
        if (normalizedText.includes(normalizedSearch)) return true;
        
        // Match berdasarkan kategori
        const categoryMap = {
            'daerah': ['depok', 'daerah', 'asal', 'pasien'],
            'resiko': ['resiko', 'preeklampsia', 'normal', 'beresiko', 'risiko'],
            'kehadiran': ['hadir', 'kehadiran', 'pasien'],
            'nifas': ['nifas', 'kfi', 'pasien'],
            'pemantauan': ['pemantauan', 'sehat', 'dirujuk', 'meninggal']
        };
        
        if (component.category && categoryMap[component.category]) {
            return categoryMap[component.category].some(keyword => 
                normalizedSearch.includes(keyword) || keyword.includes(normalizedSearch)
            );
        }
        
        // Untuk table rows
        if (component.type === 'table-row' && component.data) {
            const fields = Object.values(component.data);
            return fields.some(field => 
                this.normalizeText(field).includes(normalizedSearch)
            );
        }
        
        return false;
    }
    
    performSearch(searchTerm) {
        if (!searchTerm || searchTerm.trim() === '') {
            this.resetDashboard();
            return;
        }
        
        const term = searchTerm.toLowerCase().trim();
        let hasAnyMatches = false;
        const matchedCategories = new Set();
        
        this.components.forEach(comp => {
            if (this.componentMatches(comp, term)) {
                comp.element.style.display = '';
                comp.element.classList.remove('search-hidden');
                this.highlightMatches(comp.element, term);
                hasAnyMatches = true;
                if (comp.category) matchedCategories.add(comp.category);
            } else {
                comp.element.style.display = 'none';
                comp.element.classList.add('search-hidden');
            }
        });
        
        this.handleSectionVisibility(matchedCategories, term);
        this.showNoResults(!hasAnyMatches);
    }
    
    handleSectionVisibility(matchedCategories, searchTerm) {
        const mainSection = document.querySelector('section.grid');
        if (mainSection) {
            const hasVisibleCards = Array.from(mainSection.children)
                .some(child => !child.classList.contains('search-hidden') && 
                               window.getComputedStyle(child).display !== 'none');
            mainSection.style.display = hasVisibleCards ? 'grid' : 'none';
        }
        
        const tableSection = document.querySelector('section:has(table)');
        if (tableSection) {
            const table = tableSection.querySelector('table');
            const hasVisibleRows = table && 
                Array.from(table.querySelectorAll('tbody tr'))
                    .some(tr => !tr.classList.contains('search-hidden'));
            
            tableSection.style.display = hasVisibleRows ? 'block' : 'none';
            
            if (searchTerm.includes('pre') || searchTerm.includes('eklampsia') || 
                searchTerm.includes('tabel') || searchTerm.includes('data')) {
                tableSection.style.display = 'block';
            }
        }
    }
    
    highlightMatches(element, searchTerm) {
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function(node) {
                    if (node.parentElement.tagName === 'INPUT' || 
                        node.parentElement.tagName === 'BUTTON' ||
                        node.parentElement.tagName === 'TEXTAREA' ||
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
            const normalizedText = this.normalizeText(text);
            const normalizedSearch = this.normalizeText(searchTerm);
            
            if (normalizedText.includes(normalizedSearch)) {
                const span = document.createElement('span');
                span.innerHTML = text.replace(
                    new RegExp(`(${this.escapeRegExp(searchTerm)})`, 'gi'),
                    '<mark class="bg-yellow-300 px-1 rounded font-bold dashboard-highlight">$1</mark>'
                );
                textNode.parentNode.replaceChild(span, textNode);
            }
        });
    }
    
    removeHighlights() {
        document.querySelectorAll('mark.dashboard-highlight').forEach(mark => {
            const parent = mark.parentNode;
            const text = document.createTextNode(mark.textContent);
            parent.replaceChild(text, mark);
            parent.normalize();
        });
    }
    
    escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    resetDashboard() {
        this.removeHighlights();
        
        this.components.forEach(comp => {
            comp.element.style.display = '';
            comp.element.classList.remove('search-hidden');
            
            if (this.originalState[comp.id]) {
                comp.element.innerHTML = this.originalState[comp.id].html;
            }
        });
        
        document.querySelectorAll('section').forEach(section => {
            section.style.display = '';
        });
        
        this.showNoResults(false);
    }
    
    showNoResults(show) {
        let noResultsMsg = document.getElementById('noResultsMessage');
        
        if (show && !noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.id = 'noResultsMessage';
            noResultsMsg.className = 'mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-center';
            noResultsMsg.innerHTML = `
                <p class="text-yellow-700 font-medium">
                    Tidak ditemukan data yang cocok dengan pencarian "<span class="font-bold" id="searchTermText"></span>"
                </p>
                <p class="text-yellow-600 text-sm mt-1">Coba dengan kata kunci lain</p>
            `;
            
            this.searchInput.parentNode.parentNode.appendChild(noResultsMsg);
        }
        
        if (noResultsMsg) {
            noResultsMsg.style.display = show ? 'block' : 'none';
            if (show) {
                document.getElementById('searchTermText').textContent = this.searchInput.value;
            }
        }
    }
    
    init() {
        let timeout;
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.performSearch(e.target.value);
            }, 300);
        });
        
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.searchInput.value = '';
                this.resetDashboard();
                this.searchInput.blur();
            }
        });
        
        this.addClearButton();
    }
    
    addClearButton() {
        const wrapper = this.searchInput.parentNode;
        wrapper.classList.add('relative');
        
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'absolute inset-y-0 right-3 flex items-center hidden';
        clearBtn.innerHTML = `
            <svg class="w-4 h-4 text-gray-400 hover:text-red-500 transition-colors" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" 
                      stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        `;
        clearBtn.title = 'Bersihkan pencarian';
        
        clearBtn.addEventListener('click', () => {
            this.searchInput.value = '';
            this.resetDashboard();
            this.searchInput.focus();
            clearBtn.classList.add('hidden');
        });
        
        wrapper.appendChild(clearBtn);
        
        this.searchInput.addEventListener('input', () => {
            clearBtn.classList.toggle('hidden', this.searchInput.value.length === 0);
        });
    }
}

// ============================================================================
// 3. AUTO-INITIALIZATION FUNCTIONS
// ============================================================================

/**
 * Initialize table search for pages with data-search="true" attribute
 */
export function initPuskesmasSearch(options = {}) {
    const searchInputs = document.querySelectorAll('input[data-search="true"]');
    
    if (searchInputs.length > 0) {
        searchInputs.forEach(input => {
            const tableBody = document.querySelector(input.getAttribute('data-target') || '#dataTableBody');
            if (tableBody) {
                new PuskesmasSearch({
                    searchInputId: input.id,
                    tableBodyId: tableBody.id,
                    ...options
                });
            }
        });
    }
}

/**
 * Initialize dashboard search (halaman dashboard khusus)
 */
export function initDashboardSearch() {
    if (document.getElementById('dashboardSearch') && 
        document.querySelector('section.grid')) {
        new DashboardSearch();
    }
}

/**
 * Auto-initialize based on page type
 */
document.addEventListener('DOMContentLoaded', function() {
    // Cek jika di halaman dashboard (ada grid section)
    if (document.querySelector('section.grid')) {
        initDashboardSearch();
    } 
    // Cek jika di halaman lain dengan tabel search
    else if (document.querySelector('input[data-search="true"]')) {
        initPuskesmasSearch();
    }
});