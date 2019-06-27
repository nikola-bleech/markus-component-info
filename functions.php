<?php

namespace Flynt\Features\ComponentInfo;

use Flynt\ComponentManager;
use Flynt\Utils\Asset;

define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');

add_action('admin_menu', NS . 'componentInfoCreateMenu');

function componentInfoCreateMenu()
{
    add_menu_page('Component Info', 'Component Info', 'administrator', 'componentInfo', NS . 'componentInfoPage', 'dashicons-align-left');
}

function componentInfoPage()
{
    $output = '<div class="wrap componentInfo" is="flynt-component-info">';
    $output .= '<h1>Component Info</h1>';
    $output .= '<table class="componentTable"><thead><th>Component</th><th>Pages</th></thead><tbody>';
    $output .= renderComponents(getComponents());
    $output .= '</tbody></table>';
    $output .= '</div>';
    echo $output;
}

function renderComponents($components)
{
    $html = array_reduce(array_keys($components), function ($output, $key) use ($components) {
        $count = count($components[$key]);
        $output .= "<tr><td class='componentTable-component'><div class='componentTable-componentName'>{$key} ({$count})</div>";
        $output .= "<button class='openAllLinks'>Open All Pages</button>";
        $output .= "</td>";
        $output .= "<td class='componentTable-pages'><ul>";
        $output .= array_reduce($components[$key], function ($output, $component) {
            $output .= "<li><a target='_blank' href='{$component->url}'>{$component->post_title}</a></li>";
            return $output;
        }, '');
        $output .= '</ul></td></tr>';
        return $output;
    }, '');
    return $html;
}

function getComponents()
{
    global $wpdb;

    $componentManager = ComponentManager::getInstance();
    $allComponents = $componentManager->getAll();

    $allComponentsNames = (isset($_GET['componentName'])) ? [$_GET['componentName']] : array_keys($allComponents);

    $components = array_reduce($allComponentsNames, function ($output, $name) use ($wpdb) {
        $filterByPostType = (isset($_GET['postType'])) ? "{$wpdb->prefix}posts.post_type = '{$_GET["postType"]}' AND " : '';
        $results = $wpdb->get_results("SELECT {$wpdb->prefix}posts.post_title, {$wpdb->prefix}posts.ID, {$wpdb->prefix}postmeta.meta_value FROM {$wpdb->prefix}posts LEFT JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID WHERE {$wpdb->prefix}posts.post_status = 'publish' AND ". $filterByPostType . "{$wpdb->prefix}postmeta.meta_value LIKE '%\"" . $name . "\"%' GROUP BY {$wpdb->prefix}posts.ID ORDER BY {$wpdb->prefix}posts.post_date DESC", OBJECT);
        $components = array_map(function ($value) {
            $value->url = get_permalink($value->ID);
            return $value;
        }, $results);

        if (count($components) > 0) {
            $output[$name] = $components;
        }

        return $output;
    }, []);
    return $components;
}
