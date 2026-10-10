<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$textdomain = $this->profile_magic;
$identifier = 'GROUPS';
$path =  plugin_dir_url(__FILE__);
$pagenum = filter_input(INPUT_GET, 'pagenum');
$pagenum = isset($pagenum) ? absint($pagenum) : 1;

$attributes = is_array( $content ) ? $content : array();
$defaults = array(
    'view'             => '',
    'sort'             => '',
    'sortby'           => '',
    'limit'            => '',
    'type'             => '',
    'include'          => '',
    'exclude'          => '',
    'paid'             => '',
    'ids'              => '',
    'sorting_dropdown' => null,
    'view_icon'        => null,
    'search_box'       => null,
    'pg_source'        => '',
);
$atts = shortcode_atts( $defaults, $attributes );

// Handle legacy 'ids' parameter - if someone uses the old shortcode format
if (isset($content['ids']) && !empty($content['ids']) && empty($atts['include']) && empty($atts['ids'])) {
    $atts['ids'] = $content['ids'];
}

$global_sort = $dbhandler->get_global_option_value( 'pm_default_groups_sorting', 'newest' );
$global_view = $dbhandler->get_global_option_value( 'pm_default_groups_view', 'grid' );
$global_limit = (int) $dbhandler->get_global_option_value( 'pm_default_no_of_groups', '10' );
$grid_columns = $dbhandler->get_global_option_value( 'pm_default_groups_grid_columns', '3' );

$view_raw = strtolower( (string) $atts['view'] );
$sort_raw = $atts['sort'] ? $atts['sort'] : $atts['sortby'];
$sort_raw = strtolower( (string) $sort_raw );
$type_raw = strtolower( (string) $atts['type'] );

$view = in_array( $view_raw, array( 'grid', 'list' ), true ) ? $view_raw : $global_view;
$sort = in_array( $sort_raw, array( 'newest', 'oldest', 'name_asc', 'name_desc' ), true ) ? $sort_raw : $global_sort;
$limit = absint( $atts['limit'] );
$limit = $limit > 0 ? $limit : $global_limit;
$type = in_array( $type_raw, array( 'open', 'closed' ), true ) ? $type_raw : '';

$include_raw = $atts['include'] ? $atts['include'] : $atts['ids'];
$include_ids = array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', (string) $include_raw, -1, PREG_SPLIT_NO_EMPTY ) ) );
$exclude_ids = array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', (string) $atts['exclude'], -1, PREG_SPLIT_NO_EMPTY ) ) );

$paid = null;
if ( is_string( $atts['paid'] ) && $atts['paid'] !== '' ) {
    $paid = filter_var( $atts['paid'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
}

$pg_parse_bool_attr = function( $value, $default ) {
    if ( $value === null ) {
        return $default;
    }
    if ( is_bool( $value ) ) {
        return $value;
    }
    $parsed = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
    if ( $parsed === null ) {
        return $default;
    }
    return $parsed;
};

$pm_show_sorting_dropdown = $pg_parse_bool_attr( $atts['sorting_dropdown'] ?? null, true );
$pm_show_view_icon        = $pg_parse_bool_attr( $atts['view_icon'] ?? null, true );
$pm_show_search_box       = $pg_parse_bool_attr( $atts['search_box'] ?? null, true );

$pg_groups_settings = array(
    'view'             => $view,
    'sortby'           => $sort,
    'limit'            => $limit,
    'sorting_dropdown' => $pm_show_sorting_dropdown,
    'view_icon'        => $pm_show_view_icon,
    'search_box'       => $pm_show_search_box,
    'grid_columns'     => $grid_columns,
    'source'           => $atts['pg_source'],
);
$pg_groups_filters = array(
    'include' => $include_ids,
    'exclude' => $exclude_ids,
    'type'    => $type,
    'paid'    => $paid,
);

$themepath = $this->profile_magic_get_pm_theme('groups-tpl');
include $themepath;
?>
