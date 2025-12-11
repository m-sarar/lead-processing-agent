document.addEventListener('DOMContentLoaded', function() {
    console.log('Phonetics Platform loaded');

    // Initialize any interactive elements
    setupEventListeners();
});

function setupEventListeners() {
    // Add any event listeners here
    console.log('Event listeners set up');
}

// Audio player functionality
class AudioPlayer {
    constructor(audioElement) {
        this.audio = audioElement;
        this.isPlaying = false;
    }

    play() {
        this.audio.play();
        this.isPlaying = true;
    }

    pause() {
        this.audio.pause();
        this.isPlaying = false;
    }

    togglePlay() {
        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }
}

// Utility functions
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
}

// Language selector functionality
function setupLanguageSelector() {
    const languageSelect = document.getElementById('language-select');
    if (languageSelect) {
        languageSelect.addEventListener('change', function() {
            const lang = this.value;
            console.log('Language changed to:', lang);
            // In a real implementation, this would change the language
        });
    }
}

// Initialize the application
function initApp() {
    setupLanguageSelector();
    console.log('Application initialized');
}

// Call init when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}