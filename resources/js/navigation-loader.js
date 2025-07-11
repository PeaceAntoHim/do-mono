document.addEventListener('DOMContentLoaded', () => {
    // Get all admin panel links that navigate to other pages
    const links = document.querySelectorAll('a[href^="/"], nav a, [x-data] a, button[wire\\:click*="visit"], button[wire\\:click*="redirect"]');
    
    // Handler for showing loading state
    const handleClick = (event) => {
        // Skip if modifier keys are pressed (like new tab, download, etc.)
        if (event.ctrlKey || event.metaKey || event.shiftKey) return;
        
        // Skip for links with target="_blank" or download attributes
        const element = event.currentTarget;
        if (element.tagName === 'A' && (element.target === '_blank' || element.hasAttribute('download'))) return;
        
        // Skip for links that are prevented by other handlers
        if (event.defaultPrevented) return;
        
        // Show the page loader
        const loader = document.querySelector('[wire\\:navigate\\.init]');
        if (loader) {
            loader.classList.remove('hidden');
            loader.style.display = 'flex';
        }
    };
    
    // Add click event listeners to all links
    links.forEach(link => {
        link.addEventListener('click', handleClick);
    });
    
    // Also intercept Livewire navigation
    document.addEventListener('livewire:navigating', () => {
        const loader = document.querySelector('[wire\\:navigate\\.init]');
        if (loader) {
            loader.classList.remove('hidden');
            loader.style.display = 'flex';
        }
    });
    
    // Hide loader when navigation is complete
    document.addEventListener('livewire:navigated', () => {
        const loader = document.querySelector('[wire\\:navigate\\.init]');
        if (loader) {
            loader.classList.add('hidden');
            loader.style.display = 'none';
        }
    });
}); 