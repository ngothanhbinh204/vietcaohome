/**
 * Currency Switcher
 * 
 * Switch hiển thị giá tiền (VND / USD / TWD).
 * 
 * NGUYÊN TẮC:
 * - TUYỆT ĐỐI không tính toán tiền tệ
 * - Chỉ đọc data từ data-attributes
 * - Không AJAX / REST API
 * - Lưu state vào localStorage
 * - Không reload page
 * 
 * @package ViethomeCao
 * @since 1.0.0
 */

(function() {
    'use strict';
    
    // Config
    const CONFIG = {
        storageKey: 'vhc_selected_currency',
        defaultCurrency: 'USD',
        selector: {
            block: '.property-price-block',
            switcher: '.currency-switcher',
            button: '.currency-btn',
            priceGroup: '.price-group',
            globalSelect: '.vhc-currency-select'
        }
    };
    
    /**
     * Currency Switcher Class
     */
    class CurrencySwitcher {
        constructor() {
            this.currentCurrency = this.loadCurrency();
            this.init();
        }
        
        /**
         * Khởi tạo
         */
        init() {
            // Đợi DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }
        
        /**
         * Setup sau khi DOM ready
         */
        setup() {
            // 1. Setup global selectors (dropdowns)
            this.setupGlobalSelects();

            // 2. Setup property blocks
            const blocks = document.querySelectorAll(CONFIG.selector.block);
            if (blocks.length > 0) {
                blocks.forEach(block => this.setupBlock(block));
            }
            
            // 3. Apply saved currency
            this.switchCurrency(this.currentCurrency, false);
        }

        /**
         * Setup Global Select Dropdowns (Custom UI + Hidden Select)
         */
        setupGlobalSelects() {
            // Find all custom dropdowns directly
            const customDropdowns = document.querySelectorAll('.vhc-custom-dropdown');
            
            customDropdowns.forEach(customUI => {
                // Find parent wrapper
                const widget = customUI.closest('.vhc-currency-switcher-widget');
                if (!widget) return;

                // Find associated elements
                const select = widget.querySelector('select.vhc-currency-select');
                const trigger = customUI.querySelector('.vhc-dropdown-trigger');
                const items = customUI.querySelectorAll('.vhc-dropdown-item');

                if (!select || !trigger) {
                    console.warn('VHC: Missing select or trigger in widget', widget);
                    return;
                }

                // 1. Sync initial state
                select.value = this.currentCurrency;
                this.updateCustomUI(widget, this.currentCurrency);

                // 2. Click Trigger -> Toggle Dropdown
                trigger.onclick = (e) => { // Use onclick to avoid duplicate listeners issues
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Close other dropdowns
                    document.querySelectorAll('.vhc-custom-dropdown').forEach(el => {
                        if (el !== customUI) el.classList.remove('active');
                    });
                    
                    customUI.classList.toggle('active');
                };

                // 3. Click Item -> Select Value
                items.forEach(item => {
                    item.onclick = (e) => { // Use onclick
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const value = item.getAttribute('data-value');
                        
                        // Update Hidden Select
                        select.value = value;
                        // Dispatch Change (Standard Flow)
                        select.dispatchEvent(new Event('change'));
                        
                        // Close
                        customUI.classList.remove('active');
                    };
                });

                // 4. Native Select Change -> Update UI
                // Only bind once
                if (!select.dataset.bound) {
                    select.addEventListener('change', (e) => {
                        const newCurrency = e.target.value;
                        this.switchCurrency(newCurrency, true);
                        this.updateCustomUI(widget, newCurrency);
                    });
                    select.dataset.bound = true;
                }
            });

            // Close when clicking outside
            if (!document.body.dataset.vhcBound) {
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.vhc-custom-dropdown')) {
                        document.querySelectorAll('.vhc-custom-dropdown.active').forEach(el => {
                            el.classList.remove('active');
                        });
                    }
                });
                document.body.dataset.vhcBound = true;
            }
        }
        
        /**
         * Setup một price block
         * 
         * @param {HTMLElement} block Price block element
         */
        setupBlock(block) {
            const switcher = block.querySelector(CONFIG.selector.switcher);
            
            if (!switcher) {
                return;
            }
            
            // Bind events cho các nút
            const buttons = switcher.querySelectorAll(CONFIG.selector.button);
            
            buttons.forEach(button => {
                const currency = button.getAttribute('data-currency');
                
                if (!currency) {
                    return;
                }
                
                // Click event
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.switchCurrency(currency, true);
                });
                
                // Set active state cho currency hiện tại
                if (currency === this.currentCurrency) {
                    button.classList.add('active');
                }
            });
        }
        
        /**
         * Switch sang currency khác
         * 
         * @param {string} currency Currency code (VND, USD, TWD)
         * @param {boolean} saveToStorage Có lưu vào localStorage không
         */
        switchCurrency(currency, saveToStorage = true) {
            if (!currency) {
                return;
            }
            
            currency = currency.toUpperCase();
            this.currentCurrency = currency;
            
            // Lưu vào localStorage & Cookie
            if (saveToStorage) {
                this.saveCurrency(currency);
                this.setCookie('vhc_currency', currency, 30); // 30 days
            }
            
            // Update UI
            this.updateAllBlocks(currency);
            this.updateGlobalSelects(currency);
        }

        /**
         * Update tất cả global selects để đồng bộ
         */
        updateGlobalSelects(currency) {
            const widgets = document.querySelectorAll('.vhc-currency-switcher-widget');
            widgets.forEach(widget => {
                const select = widget.querySelector(CONFIG.selector.globalSelect);
                if (select) {
                    select.value = currency; // Update value
                    this.updateCustomUI(widget, currency); // Update Visuals
                }
            });
        }

        /**
         * Update UI Text & Active State based on currency
         */
        updateCustomUI(widget, currency) {
            const label = widget.querySelector('.vhc-current-label');
            const items = widget.querySelectorAll('.vhc-dropdown-item');
            
            // Update Label Text (find label from items or define map)
            const activeItem = Array.from(items).find(item => item.getAttribute('data-value') === currency);
            if (activeItem && label) {
                label.textContent = activeItem.textContent; 
                
                // Update selected class
                items.forEach(i => i.classList.remove('selected'));
                activeItem.classList.add('selected');
            }
        }

        
        /**
         * Set Cookie Helper
         */
        setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }
        
        /**
         * Update tất cả price blocks
         * 
         * @param {string} currency Currency code
         */
        updateAllBlocks(currency) {
            const blocks = document.querySelectorAll(CONFIG.selector.block);
            
            blocks.forEach(block => {
                this.updateBlock(block, currency);
            });
        }
        
        /**
         * Update một price block
         * 
         * @param {HTMLElement} block Price block element
         * @param {string} currency Currency code
         */
        updateBlock(block, currency) {
            // Hide tất cả price groups
            const allGroups = block.querySelectorAll(CONFIG.selector.priceGroup);
            allGroups.forEach(group => {
                group.style.display = 'none';
            });
            
            // Show price group của currency được chọn
            const activeGroup = block.querySelector(
                `[data-currency-group="${currency}"]`
            );
            
            if (activeGroup) {
                activeGroup.style.display = 'block';
            }
            
            // Update active state cho buttons
            const buttons = block.querySelectorAll(CONFIG.selector.button);
            buttons.forEach(button => {
                const btnCurrency = button.getAttribute('data-currency');
                
                if (btnCurrency === currency) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
            
            // Trigger custom event để theme có thể hook vào
            const event = new CustomEvent('vhc:currencyChanged', {
                detail: {
                    currency: currency,
                    block: block
                },
                bubbles: true
            });
            block.dispatchEvent(event);
        }
        
        /**
         * Lưu currency vào localStorage
         * 
         * @param {string} currency Currency code
         */
        saveCurrency(currency) {
            try {
                localStorage.setItem(CONFIG.storageKey, currency);
            } catch (e) {
                // Fallback nếu localStorage bị disable
                console.warn('Cannot save currency to localStorage:', e);
            }
        }
        
        /**
         * Lấy currency từ localStorage
         * 
         * @returns {string} Currency code
         */
        loadCurrency() {
            try {
                const saved = localStorage.getItem(CONFIG.storageKey);
                
                if (saved && this.isValidCurrency(saved)) {
                    return saved.toUpperCase();
                }
            } catch (e) {
                console.warn('Cannot load currency from localStorage:', e);
            }
            
            return CONFIG.defaultCurrency;
        }
        
        /**
         * Kiểm tra currency có hợp lệ không
         * 
         * @param {string} currency Currency code
         * @returns {boolean}
         */
        isValidCurrency(currency) {
            const validCurrencies = ['VND', 'USD', 'TWD'];
            return validCurrencies.includes(currency.toUpperCase());
        }
        
        /**
         * Get currency hiện tại
         * 
         * @returns {string}
         */
        getCurrentCurrency() {
            return this.currentCurrency;
        }
    }
    
    /**
     * Khởi tạo Currency Switcher
     */
    const switcher = new CurrencySwitcher();
    
    /**
     * Expose API cho global scope (để theme có thể dùng)
     */
    window.VHC = window.VHC || {};
    window.VHC.currencySwitcher = {
        /**
         * Lấy currency hiện tại
         */
        getCurrency: function() {
            return switcher.getCurrentCurrency();
        },
        
        /**
         * Switch sang currency khác
         * 
         * @param {string} currency Currency code
         */
        switchTo: function(currency) {
            switcher.switchCurrency(currency, true);
        },
        
        /**
         * Refresh tất cả price blocks
         * (Dùng khi load động content mới)
         */
        refresh: function() {
            switcher.setup();
        }
    };
    
    /**
     * Handle AJAX loaded content (nếu theme dùng infinite scroll, etc.)
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Lắng nghe các events phổ biến của infinite scroll / AJAX
        const ajaxEvents = [
            'post-load',      // Infinite scroll
            'facetwp-loaded', // FacetWP
            'sf:ajaxfinish',  // SearchAndFilter
            'jet-filter-content-rendered' // JetSmartFilters
        ];
        
        ajaxEvents.forEach(eventName => {
            document.addEventListener(eventName, function() {
                // Đợi một chút để DOM update xong
                setTimeout(() => {
                    switcher.setup();
                }, 100);
            });
        });
    });
    
})();
