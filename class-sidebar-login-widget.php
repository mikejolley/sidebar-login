<?php
/**
 * Legacy Widget
 *
 * Registers a widget with the old class name before namespaces were implemented.
 * This allows existing usage of the_widget( 'Sidebar_Login_Widget' ) to function.
 *
 * @package SidebarLogin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Legacy Widget to support previous the_widget usage.
 */
class Sidebar_Login_Widget extends \MJ\SidebarLogin\Widget {

}
