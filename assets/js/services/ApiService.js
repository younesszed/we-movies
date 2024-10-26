export class ApiService {
    constructor(baseUrl = '/api') {
        this.baseUrl = baseUrl;
    }

    async searchMovies(query) {
        const response = await fetch(`${this.baseUrl}/movies/search?query=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    }
    async autocompleteMoviesSearch(query) {
        const response = await fetch(`${this.baseUrl}/movies/autocomplete?query=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    }

    async filterMovies(formData) {

        const response = await fetch(`${this.baseUrl}/movies/by-genres`,
            {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            });
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    }

    async getMovieDetails(id) {
        const response = await fetch(`${this.baseUrl}/movie/${id}/modal`);
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    }
}