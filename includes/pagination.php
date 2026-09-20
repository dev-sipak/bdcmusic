<?php
/**
 * Reusable Pagination Helper
 *
 * Provides database-level pagination and HTML rendering.
 * Include: require_once __DIR__ . '/pagination.php';
 */

if ( ! defined( 'PAGINATION_LOADED' ) ) {
    define( 'PAGINATION_LOADED', true );
}

/**
 * Paginate a database query.
 *
 * @param PDO    $pdo       Database connection.
 * @param string $baseSql   SQL query WITHOUT LIMIT/OFFSET. Must use named placeholders.
 * @param array  $params    Named placeholder values for the base query.
 * @param int    $currentPage Current page number (1-based).
 * @param int    $perPage   Items per page.
 * @return array { items, currentPage, totalPages, totalRecords, perPage, hasPrev, hasNext, prevPage, nextPage, offset }
 */
function paginate( $pdo, $baseSql, $params = [], $currentPage = 1, $perPage = 10 ) {
    $currentPage = max( 1, (int) $currentPage );
    $perPage     = max( 1, min( 100, (int) $perPage ) );

    // Count total records
    $countSql = 'SELECT COUNT(*) as total FROM (' . $baseSql . ') AS _count_table';
    $countStmt = $pdo->prepare( $countSql );
    $countStmt->execute( $params );
    $totalRecords = (int) $countStmt->fetchColumn();

    // Calculate pagination values
    $totalPages = $totalRecords > 0 ? (int) ceil( $totalRecords / $perPage ) : 1;
    $currentPage = min( $currentPage, max( 1, $totalPages ) );
    $offset = ( $currentPage - 1 ) * $perPage;

    // Fetch items
    $itemsSql = $baseSql . ' LIMIT :limit OFFSET :offset';
    $itemsStmt = $pdo->prepare( $itemsSql );
    foreach ( $params as $key => $value ) {
        $itemsStmt->bindValue( $key, $value );
    }
    $itemsStmt->bindValue( ':limit', $perPage, PDO::PARAM_INT );
    $itemsStmt->bindValue( ':offset', $offset, PDO::PARAM_INT );
    $itemsStmt->execute();
    $items = $itemsStmt->fetchAll();

    return [
        'items'        => $items,
        'currentPage'  => $currentPage,
        'totalPages'   => $totalPages,
        'totalRecords' => $totalRecords,
        'perPage'      => $perPage,
        'hasPrev'      => $currentPage > 1,
        'hasNext'      => $currentPage < $totalPages,
        'prevPage'     => $currentPage > 1 ? $currentPage - 1 : null,
        'nextPage'     => $currentPage < $totalPages ? $currentPage + 1 : null,
        'offset'       => $offset,
    ];
}

/**
 * Build pagination data from a total count (for JS-driven pages).
 *
 * Useful when the data is already fetched and you just need the pagination metadata.
 *
 * @param int $totalRecords Total number of records.
 * @param int $currentPage  Current page (1-based).
 * @param int $perPage      Items per page.
 * @return array Same structure as paginate() but without items.
 */
function paginate_meta( $totalRecords, $currentPage = 1, $perPage = 10 ) {
    $currentPage   = max( 1, (int) $currentPage );
    $perPage       = max( 1, min( 100, (int) $perPage ) );
    $totalRecords  = max( 0, (int) $totalRecords );
    $totalPages    = $totalRecords > 0 ? (int) ceil( $totalRecords / $perPage ) : 1;
    $currentPage   = min( $currentPage, max( 1, $totalPages ) );
    $offset        = ( $currentPage - 1 ) * $perPage;

    return [
        'currentPage'  => $currentPage,
        'totalPages'   => $totalPages,
        'totalRecords' => $totalRecords,
        'perPage'      => $perPage,
        'hasPrev'      => $currentPage > 1,
        'hasNext'      => $currentPage < $totalPages,
        'prevPage'     => $currentPage > 1 ? $currentPage - 1 : null,
        'nextPage'     => $currentPage < $totalPages ? $currentPage + 1 : null,
        'offset'       => $offset,
    ];
}

/**
 * Render pagination HTML.
 *
 * @param array  $pagination Pagination data from paginate() or paginate_meta().
 * @param string $baseUrl    Base URL for page links (current page URL without query string).
 * @param array  $params     Extra query parameters to preserve (e.g. ['status' => 'pending', 'search' => 'john']).
 * @return string HTML markup.
 */
function render_pagination( $pagination, $baseUrl = '', $params = [] ) {
    if ( $pagination['totalPages'] <= 1 ) {
        return '';
    }

    $currentPage = $pagination['currentPage'];
    $totalPages  = $pagination['totalPages'];
    $hasPrev     = $pagination['hasPrev'];
    $hasNext     = $pagination['hasNext'];
    $prevPage    = $pagination['prevPage'];
    $nextPage    = $pagination['nextPage'];

    // Build base query string from params
    $queryString = http_build_query( $params );
    $separator   = $queryString ? '&' : '';

    // Determine page number range (show max 7 page buttons)
    $maxVisible = 7;
    $startPage  = max( 1, $currentPage - (int) floor( $maxVisible / 2 ) );
    $endPage    = min( $totalPages, $startPage + $maxVisible - 1 );

    // Adjust start if we're near the end
    if ( $endPage - $startPage < $maxVisible - 1 ) {
        $startPage = max( 1, $endPage - $maxVisible + 1 );
    }

    $html = '<nav class="pagination" aria-label="Page navigation">';

    // Previous button
    if ( $hasPrev ) {
        $html .= '<a class="pagination__link pagination__prev" href="'
               . htmlspecialchars( $baseUrl . '?' . $queryString . $separator . 'page=' . $prevPage )
               . '" aria-label="Previous page">&laquo; Previous</a>';
    } else {
        $html .= '<span class="pagination__link pagination__prev pagination__link--disabled" aria-disabled="true">&laquo; Previous</span>';
    }

    // First page + ellipsis
    if ( $startPage > 1 ) {
        $html .= '<a class="pagination__link" href="'
               . htmlspecialchars( $baseUrl . '?' . $queryString . $separator . 'page=1' )
               . '">1</a>';
        if ( $startPage > 2 ) {
            $html .= '<span class="pagination__ellipsis">&hellip;</span>';
        }
    }

    // Page numbers
    for ( $i = $startPage; $i <= $endPage; $i++ ) {
        if ( $i === $currentPage ) {
            $html .= '<span class="pagination__link pagination__link--active" aria-current="page">'
                   . $i . '</span>';
        } else {
            $html .= '<a class="pagination__link" href="'
                   . htmlspecialchars( $baseUrl . '?' . $queryString . $separator . 'page=' . $i )
                   . '">' . $i . '</a>';
        }
    }

    // Last page + ellipsis
    if ( $endPage < $totalPages ) {
        if ( $endPage < $totalPages - 1 ) {
            $html .= '<span class="pagination__ellipsis">&hellip;</span>';
        }
        $html .= '<a class="pagination__link" href="'
               . htmlspecialchars( $baseUrl . '?' . $queryString . $separator . 'page=' . $totalPages )
               . '">' . $totalPages . '</a>';
    }

    // Next button
    if ( $hasNext ) {
        $html .= '<a class="pagination__link pagination__next" href="'
               . htmlspecialchars( $baseUrl . '?' . $queryString . $separator . 'page=' . $nextPage )
               . '" aria-label="Next page">Next &raquo;</a>';
    } else {
        $html .= '<span class="pagination__link pagination__next pagination__link--disabled" aria-disabled="true">Next &raquo;</span>';
    }

    $html .= '</nav>';

    // Show records count
    $start = ( $currentPage - 1 ) * $pagination['perPage'] + 1;
    $end   = min( $currentPage * $pagination['perPage'], $pagination['totalRecords'] );
    $html  = '<div class="pagination-wrapper">'
           . '<p class="pagination-info">Showing ' . $start . '–' . $end . ' of ' . $pagination['totalRecords'] . '</p>'
           . $html
           . '</div>';

    return $html;
}

/**
 * Build a pagination page URL.
 *
 * @param string $baseUrl     Base URL.
 * @param int    $page        Page number.
 * @param array  $params      Extra query parameters to preserve.
 * @return string Full URL with query string.
 */
function pagination_url( $baseUrl, $page, $params = [] ) {
    $params['page'] = $page;
    return $baseUrl . '?' . http_build_query( $params );
}
