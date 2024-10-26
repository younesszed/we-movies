import {apiService, ApiService} from '../services/ApiService'

export class MovieModal {
    constructor() {
        this.modal = null;
        this.modalBody = null;
        this.bootstrapModal = null;
        this.initializeElements();
        this.api = new ApiService();
    }

    initializeElements() {
        this.modal = document.getElementById('movieModal');
        if (!this.modal) {
            console.error('Modal element not found');
            return;
        }
        this.modalBody = this.modal.querySelector('.modal-body');
        this.bootstrapModal = new bootstrap.Modal(this.modal);
    }

    init() {
        this.initializeElements();
        this.bindModalEvents();
        this.attachCardListeners();
    }

    reinitialize() {
        this.attachCardListeners();
    }

    bindModalEvents() {
        if (!this.modal) return;

        this.modal.addEventListener('shown.bs.modal', () => {
            this.initializeTrailerElements();
        });

        this.modal.addEventListener('hidden.bs.modal', () => {
            this.handleModalClose();
        });
    }

    attachCardListeners() {
        const movieCards = document.querySelectorAll('.movie-card');
        movieCards.forEach(card => {
            if (!card.dataset.hasModalListener) {
                card.addEventListener('click', (e) => this.handleCardClick(card));
                card.dataset.hasModalListener = 'true';
            }
        });
    }

    async handleCardClick(card) {
        const movieId = card.dataset.movieId;
        if (!movieId) return;

        try {
            this.showLoading();
            this.bootstrapModal.show();
            await this.loadMovieDetails(movieId);
        } catch (error) {
            console.error('Error loading movie details:', error);
            this.showError(error.message);
        }
    }

    async loadMovieDetails(movieId) {
        try {
            const response = await this.api.getMovieDetails(movieId)

            window.reinitializeModal()

            if (this.modalBody) {
                this.modalBody.innerHTML = response.html;
                this.initializeTrailerElements();
            }
        } catch (error) {
            this.showError();
        }
    }

    initializeTrailerElements() {
        if (!this.modalBody) return;

        const posterWrapper = this.modalBody.querySelector('.poster-wrapper');
        const trailerWrapper = this.modalBody.querySelector('.trailer-wrapper');
        const trailerFrame = this.modalBody.querySelector('#trailerFrame');
        const returnButton = this.modalBody.querySelector('.return-to-poster');

        if (posterWrapper && trailerWrapper && trailerFrame) {
            posterWrapper.addEventListener('click', () => {
                if (posterWrapper.dataset.hasTrailer === 'true') {
                    posterWrapper.classList.add('d-none');
                    trailerWrapper.classList.remove('d-none');
                    trailerFrame.src = trailerFrame.dataset.src;
                }
            });

            if (returnButton) {
                returnButton.addEventListener('click', () => {
                    trailerWrapper.classList.add('d-none');
                    posterWrapper.classList.remove('d-none');
                    trailerFrame.src = 'about:blank';
                });
            }
        }
    }

    handleModalClose() {
        if (!this.modalBody) return;

        const trailerFrame = this.modalBody.querySelector('#trailerFrame');
        if (trailerFrame) {
            trailerFrame.src = 'about:blank';
        }

        const posterWrapper = this.modalBody.querySelector('.poster-wrapper');
        const trailerWrapper = this.modalBody.querySelector('.trailer-wrapper');
        if (posterWrapper && trailerWrapper) {
            posterWrapper.classList.remove('d-none');
            trailerWrapper.classList.add('d-none');
        }
    }

    showLoading() {
        if (this.modalBody) {
            this.modalBody.innerHTML = `
                <div class="d-flex justify-content-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
        }
    }

    showError() {
        if (this.modalBody) {
            this.modalBody.innerHTML = `
                <div class="alert alert-danger m-3" role="alert">
                    <h4 class="alert-heading">Error</h4>
                    <p>Failed to load movie details. Please try again later.</p>
                </div>
            `;
        }
    }
}