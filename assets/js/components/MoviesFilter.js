import {AbstractComponent} from "./AbstractComponent";

export class MoviesFilter extends AbstractComponent {
    constructor() {
        super();

        this.form = document.getElementById('genreForm');
        this.genreCheckboxes = document.querySelectorAll('.genre-checkbox');
    }

    init(movieModal) {
        if (!this.form) return;
        this.movieModal = movieModal;
        this.bindEvents();
        this.initFromURL();
    }

    bindEvents() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        this.genreCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.handleCheckboxChange());
        });
    }

    async handleSubmit(event) {
        event.preventDefault();
        await this.filterMovies();
    }

    handleCheckboxChange() {
        const submitButton = this.form.querySelector('button[type="submit"]');
        const hasSelectedGenres = Array.from(this.genreCheckboxes)
            .some(checkbox => checkbox.checked);
        submitButton.disabled = !hasSelectedGenres;
    }

    async filterMovies() {
        try {
            this.showLoading();

            const formData = new FormData(this.form);
            const params = new URLSearchParams();
            formData.getAll('genres[]').forEach(genre => {
                params.append('genres[]', genre);
            });

            const api = new ApiService()

            const response = await api.filterMovies(formData)

            this.updatePageTitle('Filtered Movies ')

            await this.updateMoviesList(response.html);

            this.updateURL();

        } catch (error) {
            console.error('Error filtering movies:', error);
            this.showError();
        }
    }

    updateURL() {
        const selectedGenres = Array.from(this.genreCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        const url = new URL(window.location);
        if (selectedGenres.length > 0) {
            url.searchParams.set('genres', selectedGenres.join(','));
        } else {
            url.searchParams.delete('genres');
        }

        window.history.pushState({}, '', url);
    }

    initFromURL() {
        const url = new URL(window.location);
        const genres = url.searchParams.get('genres');

        if (genres) {
            const selectedGenres = genres.split(',');
            this.genreCheckboxes.forEach(checkbox => {
                checkbox.checked = selectedGenres.includes(checkbox.value);
            });
            this.handleCheckboxChange();
            this.filterMovies();
        }
    }
}