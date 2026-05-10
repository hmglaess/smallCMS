// Lightbox functionality
const image = document.getElementById('myImage');
const lightbox = document.getElementById('lightbox');
const largeImage = document.getElementById('largeImage');

// Setze das große Bild bei Bedarf (wenn es ein anderes ist)
// largeImage.src = "dein_grosses_bild.jpg";

if (image && lightbox && largeImage) {
    image.addEventListener('click', () => {
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden'; // Verhindert Scrollen im Hintergrund
    });

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = ''; // Erlaubt Scrollen wieder
    }

    // Optional: Klick auf Overlay schließt Lightbox
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) { // Klick auf den Hintergrund
            closeLightbox();
        }
    });
}

// Demo: Sidebars dynamisch ein-/ausblenden
function updateLayout() {
    const main = document.getElementById("mainContent");
    const leftSidebar = document.querySelector(".sidebar-left");
    const rightSidebar = document.querySelector(".sidebar-right");

    const hasLeft = leftSidebar && leftSidebar.innerHTML.trim().length > 0;
    const hasRight = rightSidebar && rightSidebar.innerHTML.trim().length > 0;

    if (main) {
        main.className = "";

        if (hasLeft && hasRight) {
            main.classList.add("has-sidebar");
        } else if (hasLeft) {
            main.classList.add("has-left-only");
        } else if (hasRight) {
            main.classList.add("has-right-only");
        } else {
            main.classList.add("no-sidebar");
        }
    }
}

// Dynamisches Laden der Komponenten nach dem Seitenaufbau
function loadDynamicComponents() {
    // Navigation laden
    const navElement = document.querySelector('.menu');
    if (navElement) {
        htmx.ajax('GET', '/api/navigation', {target: navElement, swap: 'innerHTML'});
    }
    
    // Events laden und alle 5 Minuten aktualisieren
    const eventsElement = document.getElementById('events-container');
    if (eventsElement) {
        // Erste Ladung
        htmx.ajax('GET', '/api/events', {target: eventsElement, swap: 'innerHTML'});
        
        // Automatische Aktualisierung alle 5 Minuten (300.000 ms)
        setInterval(function() {
            htmx.ajax('GET', '/api/events', {target: eventsElement, swap: 'innerHTML'});
        }, 300000);
    }
}

// Hamburger Menu Functions
function toggleMenu() {
    const menu = document.getElementById('main-navigation');
    if (menu) {
        menu.classList.toggle('active');
        
        // Hamburger animation
        const hamburger = document.querySelector('.hamburger');
        if (hamburger) {
            hamburger.classList.toggle('active');
        }
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('main-navigation');
        const hamburger = document.querySelector('.hamburger');
        
        if (menu && hamburger && !menu.contains(event.target) && !hamburger.contains(event.target)) {
            menu.classList.remove('active');
            hamburger.classList.remove('active');
        }
    });
}

function toggleFooterSection(element) {
    console.log('toggleFooterSection called');
    
    const section = element.closest('.footer-section');
    if (section) {
        const content = section.querySelector('.footer-section-content');
        const hamburger = element;
        
        if (content && hamburger) {
            console.log('Toggling footer section');
            content.classList.toggle('active');
            hamburger.classList.toggle('active');
        } else {
            console.error('Content or hamburger not found');
        }
    } else {
        console.error('Footer section not found');
    }
}

// Initial layout check and component loading
document.addEventListener('DOMContentLoaded', function() {
    updateLayout();
    loadDynamicComponents();
    
    // Add event listeners for hamburger menus
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', toggleMenu);
    }
    
    // Add event listeners for footer hamburger menus
    const footerHamburgers = document.querySelectorAll('.footer-hamburger');
    if (footerHamburgers.length > 0) {
        footerHamburgers.forEach(hamburger => {
            hamburger.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleFooterSection(this);
            });
        });
    }
    
    // Add event listeners for footer section headers
    const footerHeaders = document.querySelectorAll('.footer-section-header');
    if (footerHeaders.length > 0) {
        footerHeaders.forEach(header => {
            header.addEventListener('click', function(e) {
                const hamburger = this.querySelector('.footer-hamburger');
                if (hamburger) {
                    e.stopPropagation();
                    toggleFooterSection(hamburger);
                }
            });
        });
    }
});