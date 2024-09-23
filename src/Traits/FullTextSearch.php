<?php

namespace ComposableQueryBuilder\Traits;

use ComposableQueryBuilder\Utils\Normalizer;
use Illuminate\Support\Str;

trait FullTextSearch
{
    public function getMatchAgainstStatement($columns, $term): string
    {
        $columns = implode(',', Normalizer::array($columns));

        return "MATCH ({$columns}) AGAINST ('{$this->fullTextWildcards($term)}' IN BOOLEAN MODE)";
    }

    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    protected function fullTextWildcards($term): string
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

        return implode(' ', $words);
    }

    public function forEachNumericToken($term, $callback): void
    {
        $tokens = explode(' ', $term);

        foreach ($tokens as $token) {
            if (strlen($token) > 4 && is_numeric($token)) {
                $callback($token);
            }
        }
    }

    public function forEachOneDigitToken($term, $callback): void
    {
        $tokens = explode(' ', $term);

        foreach ($tokens as $token) {
            if (strlen($token) == 1 && is_numeric($token)) {
                $callback($token);
            }
        }
    }
}
