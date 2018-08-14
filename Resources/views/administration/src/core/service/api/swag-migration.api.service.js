import ApiService from 'src/core/service/api/api.service';

class MigrationApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'migration') {
        super(httpClient, loginService, apiEndpoint);
    }

    fetchData(additionalParams = {}, additionalHeaders = {}) {
        const params = additionalParams;
        const headers = this.getBasicHeaders(additionalHeaders);

        return this.httpClient
            .post(`${this.getApiBasePath()}/fetch-data`, params, {
                headers
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    checkConnection() {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .post(`${this.getApiBasePath()}/check-connection`, {}, {
                headers
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default MigrationApiService;