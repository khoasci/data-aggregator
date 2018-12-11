<?php

namespace App\Http\Controllers;

use Aic\Hub\Foundation\Exceptions\DetailedException;

use App\Http\Search\Request as SearchRequest;
use App\Http\Search\Response as SearchResponse;
use Illuminate\Http\Request;
use Elasticsearch;

use Illuminate\Routing\Controller as BaseController;

class SearchController extends BaseController
{

    /*
    |--------------------------------------------------------------------------
    | Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller provides a thin API on top of Elasticsearch. It largely
    | follows ES Request Body conventions, but limits what kind of queries can
    | be performed, for security and performance reasons. Additionally, it
    | applies our own business logic to tweak relevancy.
    |
    */

    /**
     * General entry point for search. There are three modes:
     *
     *  1. If `query` is present, append it to the `must` clause.
     *  2. If `q` is present, add full-text search to the `must` clause.
     *  3. If `q` is absent, show all results.
     *
     * Broadly, we send a bool query to Elasticsearch. We put the user's queries
     * into the `must` clause, and our relevancy tweaks, into the `should` clause.
     *
     * `query` follows ES "Search Request Body" and "Query DSL" conventions.
     * `q` is a string, but it does *not* support ES's "URI Search" syntax.
     *
     * We use `q` for performing simple, opinionated full-text searches, and
     * for offering search suggestions, e.g. spelling corrections.
     *
     * Regardless of whether or not `query` is present, if `q` is present,
     * it will be used to provide "Did You Mean"-style suggestions.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-uri-request.html
     */
    public function search( Request $request, $resource = null )
    {

        return $this->query( 'getSearchParams', 'getSearchResponse', 'search', $resource );

    }


    /**
     * Multisearch functionality. Send multiple queries in one request by wrapping them in a
     * top-level indexed array.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.3/search-multi-search.html
     * @link https://discuss.elastic.co/t/elasticsearch-5-0-php-msearch/66716
     *
     * @return array
     */
    public function msearch( Request $request )
    {
        return $this->mquery( 'getSearchParams', 'getSearchResponse', $request );
    }


    /**
     * Return autocomplete suggestions, via an array of title strings.
     *
     * Relies on the `suggest` field of search. This method optimizes both our request
     * to Elasticsearch and the outgoing results for the minimum required to provide
     * autocomplete suggestions. It accepts the same params as the `search` method,
     * though most of them will not be used.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html
     */
    public function autocompleteWithTitle( Request $request, $resource = null )
    {

        return $this->query( 'getAutocompleteParams', 'getAutocompleteWithTitleResponse', 'search', $resource );

    }


    /**
     * Return autocomplete suggestions, but passes through `_source` from each result.
     * Allows us to return an array of objects: id, title, api_model.
     */
    public function autocompleteWithSource( Request $request, $resource = null )
    {

        return $this->query( 'getAutocompleteParams', 'getAutocompleteWithSourceResponse', 'search', $resource, null, [
            'use_suggest_autocomplete_all' => true,
        ]);

    }

    /**
     * Perform Elasticsearch explain query. Meant for local debugging.
     */
    public function explain( Request $request, $resource, $id )
    {

        return $this->query( 'getExplainParams', 'getRawResponse', 'explain', $resource, $id );

    }

    /**
     * Perform Elasticsearch search, but show last request sent to Elasticsearch instead.
     * Meant for local debugging.
     */
    public function echo( Request $request, $resource = null )
    {

        $this->query( 'getSearchParams', 'getSearchResponse', 'search', $resource );

        return response( $this->getRequest() )->header('Content-Type', 'application/json');

    }

    /**
     * Helper method to perform a query against Elasticsearch endpoint.
     *
     * @param string $requestMethod  Name of transformation method on SearchRequest class
     * @param string $responseMethod  Name of transformation method on SearchResponse class
     * @param array $resource Resource to search (translates to index and type)
     * @param string $id Identifier of a resource (meant for explain)
     *
     * @return array
     */
    private function query( $requestMethod, $responseMethod, $elasticsearchMethod, $resource, $id = null, $requestArgs = null )
    {

        // Transform our API's syntax into an Elasticsearch params array
        $params = ( new SearchRequest( $resource, $id ) )->$requestMethod( $requestArgs );
        $cacheKey = $this->buildCacheKey($elasticsearchMethod, $params, config('elasticsearch.cache_version'));
        $results = null;

        try {
            // Perform API request and caching
            if (config('elasticsearch.cache_enabled')) {
                $results = \Cache::remember($cacheKey, config('elasticsearch.cache_ttl'), function () use ($elasticsearchMethod, $params) {
                    return Elasticsearch::$elasticsearchMethod( $params );
                });
            }
            else {
                $results = Elasticsearch::$elasticsearchMethod( $params );
            }
        } catch (\Exception $e) {

            // Elasticsearch occasionally returns a status code of zero
            $code = $e->getCode() > 0 ? $e->getCode() : 500;

            if (config('elasticsearch.cache_enabled')) {
                \Cache::forget($cacheKey);
            }

            return response( $e->getMessage(), $code )->header('Content-Type', 'application/json');
        }

        // Transform Elasticsearch results into our API standard
        $response = ( new SearchResponse( $results, $params ) )->$responseMethod();

        return $response;

    }

    /**
     * Helper for shared multi-query functionality.
     *
     * @return array
     */
    private function mquery($requestMethod, $responseMethod, Request $request)
    {

        $queries = json_decode($request->getContent(), true);

        if( !is_array( $queries ) || count( array_filter( array_keys( $queries ), 'is_string') ) > 0 ) {

            // TODO: Accept key'd
            throw new DetailedException('Invalid Query', 'You must pass an indexed array as the root object.', 400);

        }

        $originalParams = [];

        foreach( $queries as $query )
        {
            $originalParams[] = ( new SearchRequest() )->$requestMethod( $query );
        }

        $transformedParams = [];

        foreach( $originalParams as $params )
        {

            $header = [];

            if( isset( $params['index'] ) ) {
               $header['index'] = $params['index'];
               unset( $params['index'] );
            }

            if( isset( $params['type'] ) ) {
               $header['type'] = $params['type'];
               unset( $params['type'] );
            }

            $body = [];

            if( isset( $params['body'] ) ) {
                $body = $params['body'];
                unset( $params['body'] );
            }

            foreach( ['preference', 'from', 'size'] as $key ) {
                if( array_key_exists( $key, $params ) && is_null( $params[$key] ) ) {
                    unset( $params[$key] );
                }
            }

            $body = array_merge( $params, $body );

            $transformedParams[] = $header;
            $transformedParams[] = $body;
        }

        $params = ['body' => $transformedParams];
        $cacheKey = $this->buildCacheKey('msearch', $params, config('elasticsearch.cache_version'));
        $results = null;

        try {
            // Perform API request and caching
            if (config('elasticsearch.cache_enabled')) {
                $results = \Cache::remember($cacheKey, config('elasticsearch.cache_ttl'), function () use ($params) {
                    return Elasticsearch::msearch( $params );
                });
            }
            else {
                $results = Elasticsearch::msearch( $params );
            }
        } catch (\Exception $e) {

            // Elasticsearch occasionally returns a status code of zero
            $code = $e->getCode() > 0 ? $e->getCode() : 500;

            if (config('elasticsearch.cache_enabled')) {
                \Cache::forget($cacheKey);
            }

            return response( $e->getMessage(), $code )->header('Content-Type', 'application/json');
        }

        // Reduce down to our array of interest
        $results = $results['responses'];

        $responses = [];

        foreach( $results as $result ) {

            // Transform Elasticsearch results into our API standard
            $responses[] = ( new SearchResponse( $result, $originalParams ) )->$responseMethod();

        }

        return $responses;

    }


    /**
     * Retrieve the last query sent by this client to Elasticsearch.
     *
     * @return array
     */
    private function getRequest()
    {

        $request = Elasticsearch::connection('default')->transport->lastConnection->getLastRequestInfo()['request'];
        $request['body'] = json_decode( $request['body'], true );

        return $request;

    }

    protected function buildCacheKey() {
        return md5(json_encode(func_get_args()));
    }
}
