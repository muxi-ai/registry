<?php

/**
 * Search controller provides full-text search across formations.
 */
class Search extends TinyController
{
    /**
     * Execute a user query and render matching formations.
     *
     * @param TinyRequest $request Incoming HTTP request context.
     * @param TinyResponse $response Response helper used to render a view.
     */
    public function get($request, $response)
    {
        // Extract the search query from the URL parameter.
        $query = tiny::router()->query['q'] ?? '';

        $formations = [];
        $resultCount = 0;
        $correctedQuery = null;
        $originalQuery = $query;

        // Only execute search if the user provided a non-empty query.
        if (!empty($query)) {
            // Use the search model for the primary search
            $formations = tiny::model('finder')->searchFormations($query, 'trending', 100);

            // If no results, try typo correction (Strategy 4)
            if (empty($formations) && strlen($query) >= 4) {
                $result = tiny::model('finder')->searchWithTypoCorrection($query);
                $formations = $result['formations'];
                $correctedQuery = $result['correction'];
            }

            $resultCount = count($formations);
        }

        // Pass results and the original query back to the view for rendering.
        $response->render('search', [
            'query' => $query,
            'originalQuery' => $originalQuery,
            'correctedQuery' => $correctedQuery,
            'formations' => $formations,
            'resultCount' => $resultCount,
        ]);
    }
}
