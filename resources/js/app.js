import './bootstrap';

/**
 * Theme Management
 * Handles dark/light mode with localStorage persistence
 * Requirements: 14.1, 14.2, 14.3, 14.4
 */
const ThemeManager = {
    STORAGE_KEY: 'theme',
    DARK_CLASS: 'dark',
    
    /**
     * Initialize theme based on stored preference or defaults
     * - Public pages (unauthenticated): default to dark theme
     * - Authenticated pages: default to light theme
     */
    init() {
        const storedTheme = localStorage.getItem(this.STORAGE_KEY);
        const isAuthenticated = document.body.dataset.authenticated === 'true';
        
        let isDark;
        if (storedTheme) {
            isDark = storedTheme === 'dark';
        } else {
            // Default: dark for public pages, light for authenticated
            isDark = !isAuthenticated;
        }
        
        this.applyTheme(isDark);
    },
    
    /**
     * Apply theme to document
     */
    applyTheme(isDark) {
        if (isDark) {
            document.documentElement.classList.add(this.DARK_CLASS);
        } else {
            document.documentElement.classList.remove(this.DARK_CLASS);
        }
    },
    
    /**
     * Toggle theme and persist to localStorage
     */
    toggle() {
        const isDark = document.documentElement.classList.toggle(this.DARK_CLASS);
        localStorage.setItem(this.STORAGE_KEY, isDark ? 'dark' : 'light');
        return isDark;
    },
    
    /**
     * Set specific theme
     */
    setTheme(theme) {
        const isDark = theme === 'dark';
        this.applyTheme(isDark);
        localStorage.setItem(this.STORAGE_KEY, theme);
    },
    
    /**
     * Get current theme
     */
    getCurrentTheme() {
        return document.documentElement.classList.contains(this.DARK_CLASS) ? 'dark' : 'light';
    }
};

// Initialize theme immediately to prevent flash
ThemeManager.init();

// Expose to window for Alpine.js integration
window.ThemeManager = ThemeManager;
