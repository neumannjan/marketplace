import axios, {AxiosError, AxiosRequestConfig, AxiosResponse} from 'axios';
import {ContinuousResponse, PaginatedResponse} from 'JS/api/types';

/**
 * API error
 */
export type ApiError = { http: AxiosError } | { api: object | null };

/**
 * Response or request data interface
 */
interface Data {
    [name: string]: any
}

/**
 * Data that only have keys that D has
 */
type MatchingData<D extends Data> = {
    [index in keyof D]: any
}

/**
 * Raw single API response.
 */
interface RawSingleApiResponse<ResponseData extends Data> {
    success: boolean
    result: ResponseData
}

/**
 * Raw composite API response
 */
type RawCompositeApiResponse<ResponseData extends Data, RequestData extends MatchingData<ResponseData> = MatchingData<ResponseData>> = {
    [name in keyof RequestData]: RawSingleApiResponse<ResponseData[name]>;
}

/**
 * Raw composite API response containing global request.
 */
type RawCompositeApiResponseWithGlobal<GlobalResponseData extends Data, ResponseData extends Data, RequestData extends MatchingData<ResponseData> = MatchingData<ResponseData>>
    = RawCompositeApiResponse<ResponseData, RequestData> & { global: RawSingleApiResponse<GlobalResponseData> };

/**
 * Response returned by Axios for an API request.
 */
interface AxiosApiResponse<TData extends Data> extends AxiosResponse {
    data: TData | undefined
}

export default abstract class Api<GlobalResponseData extends Data = object> {
    /**
     * Function that gets called when the API loses or regains internet connection.
     */
    protected abstract onConnection(has: boolean): void;

    /**
     * Function that returns current CSRF token.
     */
    protected abstract getCsrfToken(): string | null;

    /**
     * function that gets called once new global request response is available.
     */
    protected abstract onGlobalResponse(data: GlobalResponseData): void;

    protected get axiosConfig(): AxiosRequestConfig {
        const token = this.getCsrfToken();

        if (token === null) {
            throw "Missing CSRF token";
        }

        return {
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    }

    /**
     * Return a reject function for an HTTP error.
     * @param {(error: ApiError) => void} reject
     * @return {(error: AxiosError) => void}
     */
    protected promiseHttpErrorReject(reject: (error: ApiError) => void): ((error: AxiosError) => void) {
        return (error: AxiosError) => {
            if (error.response === undefined) {
                // Notify that we do not have access to the internet.
                this.onConnection(false);
            }

            reject({http: error});
        }
    }

    protected responseIncludesGlobal<ResponseData extends Data>(data: RawCompositeApiResponse<ResponseData>):
        data is RawCompositeApiResponseWithGlobal<GlobalResponseData, ResponseData> {
        return !!data.global;
    }

    /**
     * Perform a composite API call.
     * @param {Data} allParams Parameters of all requests
     * @param {boolean} includeGlobal Whether the 'global' request should be included
     * @return {Promise<RawCompositeApiResponse<ResponseData>>}
     */
    requestComposite<ResponseData extends Data = Data>
    (allParams: MatchingData<ResponseData>, includeGlobal: boolean = true): Promise<RawCompositeApiResponse<ResponseData>> {
        return new Promise<RawCompositeApiResponse<ResponseData>>
        ((resolve: (data: RawCompositeApiResponse<ResponseData>) => any, reject: (error: ApiError) => void) => {
            if (includeGlobal && !allParams.global)
                allParams.global = {};

            const data = {
                api: JSON.stringify(allParams)
            };

            const then = (response: AxiosApiResponse<RawCompositeApiResponse<ResponseData>>) => {
                // Notify that we have access to the internet
                this.onConnection(true);

                if (response.data) {
                    if (this.responseIncludesGlobal(response.data) && response.data.global.success) {
                        this.onGlobalResponse(response.data.global.result);
                    }

                    resolve(response.data);
                } else {
                    reject({api: null});
                }
            };

            axios.post('/api', data, this.axiosConfig)
                .then(then)
                .catch(this.promiseHttpErrorReject(reject));
        });
    }

    /**
     * Perform an API call.
     * @param {string} name Request name
     * @param {Data | FormData} params Request parameters
     * @param {boolean} includeGlobal Whether the 'global' request should be included
     * @returns {Promise<ResponseData>}
     */
    requestSingle<ResponseData extends Data = Data>
    (name: string, params: Data | FormData = {}, includeGlobal: boolean = true): Promise<ResponseData> {
        if (params instanceof FormData) {
            return this.requestByURL<ResponseData>(`/api/${name}`, params);
        }

        return new Promise<ResponseData>
        ((resolve: (data: ResponseData) => any, reject: (error: ApiError) => any) => {

            const then = (response: RawCompositeApiResponse<{ [name: string]: ResponseData }>) => {
                // Notify that we have access to the internet
                this.onConnection(true);

                let data = response[name];

                if (data) {
                    if (data.success)
                        resolve(data.result);
                    else
                        reject({api: data.result});
                } else {
                    throw response;
                }
            };

            const data = {
                [name]: params
            };

            this.requestComposite<{ [name: string]: ResponseData }>(data, includeGlobal)
                .then(then)
                .catch(reject);
        });
    }

    /**
     * @param {string} url API endpoint URL
     * @param {FormData} formData
     * @returns {Promise<ResponseData>}
     */
    requestByURL<ResponseData extends Data = Data>
    (url: string, formData?: FormData): Promise<ResponseData> {
        return new Promise<ResponseData>
        ((resolve: (data: ResponseData) => any, reject: (error: ApiError) => any) => {

            const then = (response: AxiosApiResponse<RawCompositeApiResponse<ResponseData>>) => {
                // Notify that we have access to the internet
                this.onConnection(true);

                if (response.data) {
                    let name: string | null = null;
                    for (let [key, data] of Object.entries(response.data)) {
                        if (key === 'global') {
                            if (data.success) {
                                this.onGlobalResponse(data.result);
                            }
                        } else {
                            name = key;
                        }
                    }

                    if (name && response.data[name]) {
                        const data = response.data[name];
                        if (data && data.success)
                            resolve(data.result);
                        else
                            reject({api: data ? data.result : null});
                    } else {
                        reject({api: null});
                    }
                }
            };

            axios.post(url, formData, this.axiosConfig)
                .then(then)
                .catch(this.promiseHttpErrorReject(reject));
        });
    }

    /**
     * Request to an endpoint that returns a paginated response. Next results can be easily requested.
     * @param url {string}
     */
    requestPaginated<ResponseData extends Data[] = Data[], OriginalResponseData extends Data[] = ResponseData>
    (url: string, modifyDataCallback?: (data: OriginalResponseData) => ResponseData): () => Promise<ContinuousResponse<ResponseData>> {
        return async () => {
            const result = await this.requestByURL<PaginatedResponse<OriginalResponseData>>(url);
            const nextUrl = result.next_page_url;

            return {
                data: modifyDataCallback ? modifyDataCallback(result.data) : <any>result.data,
                fetchMore: nextUrl ? this.requestPaginated<ResponseData, OriginalResponseData>(nextUrl, modifyDataCallback) : null
            };
        }
    }
}