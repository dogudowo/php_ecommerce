<?php
// 分頁功能
function generatePagination($currentPage, $totalPages, $params = [])
{
    $queryString = '';
    if (!empty($params)) {
        $queryString = '&' . http_build_query($params);
    }

    $html = '<nav aria-label="分頁導航">';
    $html .= '<ul class="pagination justify-content-center mt-4">';

    // 上一頁按鈕
    $html .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="?page=' . max(1, $currentPage - 1) . $queryString . '">上一頁</a>';
    $html .= '</li>';

    // 頁碼按鈕
    for ($i = 1; $i <= $totalPages; $i++) {
        $html .= '<li class="page-item ' . ($i === $currentPage ? 'active' : '') . '">';
        $html .= '<a class="page-link" href="?page=' . $i . $queryString . '">' . $i . '</a>';
        $html .= '</li>';
    }

    // 下一頁按鈕
    $html .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="?page=' . min($totalPages, $currentPage + 1) . $queryString . '">下一頁</a>';
    $html .= '</li>';

    $html .= '</ul>';
    $html .= '</nav>';

    return $html;
}
?>