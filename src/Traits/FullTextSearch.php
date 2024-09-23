<?php

namespace ComposableQueryBuilder\Traits;

use Illuminate\Support\Str;

trait FullTextSearch
{
    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    protected function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term            = str_replace($reservedSymbols, '', (string)Str::of($term)->trim());

        $words = explode(' ', $term);

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 2) {
                $words[$key] = '+' . $word . '*';
            }
        }

        $searchTerm = implode(' ', $words);

        return $searchTerm;
    }

    public function getMatchAgainstStatement($columns, $term)
    {
        $columns = implode(',', array_normalize($columns));

        return "MATCH ({$columns}) AGAINST ('{$this->fullTextWildcards($term)}' IN BOOLEAN MODE)";
    }

    public function forEachNumericToken($term, $callback)
    {
        $tokens = explode(' ', $term);

        foreach ($tokens as $token) {
            if (strlen($token) > 4 && is_numeric($token)) {
                $callback($token);
            }
        }
    }

    public function forEachOneDigitToken($term, $callback)
    {
        $tokens = explode(' ', $term);

        foreach ($tokens as $token) {
            if (strlen($token) == 1 && is_numeric($token)) {
                $callback($token);
            }
        }
    }
}
