import './styles/app.scss';
import { MovieAutocomplete } from './js/components/MovieAutocomplete';
import {  MoviesFilter } from './js/components/MoviesFilter';
import { MovieModal } from './js/components/MovieModal';
import { ApiService } from './js/services/ApiService';

class App {
    constructor() {
        this.api = new ApiService();
        this.modal = null;
        this.autocomplete = null;
        this.genreFilter = null;
    }

    init() {
        this.modal = new MovieModal();
        this.autocomplete = new MovieAutocomplete();
        this.genreFilter = new MoviesFilter();

        this.modal.init();
        this.autocomplete.init(this.modal);
        this.genreFilter.init(this.modal);

        window.reinitializeModal = () => {
            this.modal.reinitialize();
        };
    }
}

// Initialiser l'application au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    const app = new App();
    app.init();
});