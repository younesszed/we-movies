import { ApiService } from '../services/ApiService';

export class AbstractComponent {
    constructor() {
        this.pageTitle = document.getElementById('pageTitle')
        this.moviesList = document.getElementById('moviesList')
        this.initialized = false;
        this.api = new ApiService();
    }

    showLoading() {
        console.log('loading suggestions')
        this.moviesList.innerHTML = `
            <div class="col-12 text-center py-5 loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
    }

    showError(message) {
        this.moviesList.innerHTML = `
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    ${message}
                </div>
            </div>
        `;
    }

    updatePageTitle(title) {
        this.pageTitle.innerHTML = title
    }

    async updateMoviesList(html) {

        if (!this.moviesList) return;

        this.moviesList.innerHTML = html;

        window.reinitializeModal()
    }


    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

}