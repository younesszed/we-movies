import {AbstractComponent} from "./AbstractComponent";

export class MovieAutocomplete extends AbstractComponent {
    constructor() {
        super();

        this.searchInput = document.getElementById('searchInput');
        this.suggestionsContainer = document.getElementById('searchSuggestions');
        this.searchForm = document.getElementById('searchForm');
        this.currentSuggestions = [];
        this.selectedIndex = -1;
    }

    init() {
        if (!this.searchInput || !this.suggestionsContainer) {
            console.error('Required elements not found');
            return;
        }

        this.setupSuggestionsContainer();
        this.bindEvents();
    }

    setupSuggestionsContainer() {
        this.suggestionsContainer.style.position = 'absolute';
        this.suggestionsContainer.style.width = `${this.searchInput.offsetWidth}px`;
        this.suggestionsContainer.style.top = `${this.searchInput.offsetHeight + 2}px`;
        this.suggestionsContainer.style.left = '0';
        this.suggestionsContainer.style.zIndex = '1000';
    }

    bindEvents() {
        // Input events
        this.searchInput.addEventListener('input', this.debounce(() => {
            this.handleInput();
        }, 300));

        this.searchInput.addEventListener('focus', () => {
            if (this.currentSuggestions.length > 0) {
                this.showSuggestions();
            }
        });

        this.searchInput.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });

        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) &&
                !this.suggestionsContainer.contains(e.target)) {
                this.hideSuggestions();
            }
        });

        this.searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (this.selectedIndex >= 0) {
                this.selectSuggestion(this.currentSuggestions[this.selectedIndex].title);
            }
            this.performSearch();
        });
    }

    async performSearch() {

        const searchTerm = this.searchInput.value.trim();
        console.log(searchTerm)
        if (!searchTerm) return;

        try {
            this.showLoading();

            const response = await this.api.searchMovies(searchTerm)

            this.updatePageTitle('Search result')
            await this.updateMoviesList(response.html);

        } catch (error) {
            console.error('Search error:', error);
            this.showError('An error occurred while searching');
        }
    }

    async handleInput() {
        const query = this.searchInput.value.trim();

        if (query.length < 2) {
            this.hideSuggestions();
            return;
        }

        try {
            this.showLoading();

            const data = await this.api.autocompleteMoviesSearch(query)
            console.log(data)
            if (data.results && Array.isArray(data.results)) {
                this.currentSuggestions = data.results;
                if (this.currentSuggestions.length > 0) {
                    this.showSuggestions();
                } else {
                    this.hideSuggestions();
                }
            }
        } catch (error) {
            console.error('Autocomplete error:', error);
            this.hideSuggestions();
        }
    }

    showSuggestions() {
        const html = this.currentSuggestions.map((movie, index) => `
            <div class="suggestion-item ${index === this.selectedIndex ? 'active' : ''}"
                 data-index="${index}">
                <div class="d-flex align-items-center p-2">
                    ${movie.posterPath ?
            `<img src="https://image.tmdb.org/t/p/w92${movie.posterPath}" 
                              alt="${movie.title}"
                              class="suggestion-poster me-2"
                              style="width: 45px; height: 68px; object-fit: cover;">` :
            '<div class="suggestion-poster-placeholder me-2"></div>'
        }
                    <div class="suggestion-details">
                        <div class="suggestion-title">${movie.title}</div>
                        ${movie.releaseDate ?
            `<small class="text-muted">${new Date(movie.releaseDate).getFullYear()}</small>` :
            ''
        }
                    </div>
                </div>
            </div>
        `).join('');

        this.suggestionsContainer.innerHTML = html;
        this.suggestionsContainer.classList.remove('d-none');

        this.suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                const index = parseInt(item.dataset.index);
                this.selectSuggestion(this.currentSuggestions[index].title);
                this.performSearch();
            });

            item.addEventListener('mouseenter', () => {
                this.selectedIndex = parseInt(item.dataset.index);
                this.highlightSuggestion();
            });
        });
    }

    selectSuggestion(title) {
        this.searchInput.value = title;
        this.hideSuggestions();
    }

    highlightSuggestion() {
        const items = this.suggestionsContainer.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    handleKeydown(e) {
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(
                    this.selectedIndex + 1,
                    this.currentSuggestions.length - 1
                );
                this.highlightSuggestion();
                break;

            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.highlightSuggestion();
                break;

            case 'Enter':
                if (this.selectedIndex >= 0) {
                    e.preventDefault();
                    this.selectSuggestion(this.currentSuggestions[this.selectedIndex].title);
                    this.performSearch();
                }
                break;

            case 'Escape':
                this.hideSuggestions();
                break;
        }
    }

    hideSuggestions() {
        this.suggestionsContainer.classList.add('d-none');
        this.currentSuggestions = [];
        this.selectedIndex = -1;
    }
}

export function initAutocomplete() {
    const autocomplete = new MovieAutocomplete();
    autocomplete.init();
    return autocomplete;
}