<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Plugin Name: Docalist Biblio Thumbnails
 * Plugin URI:  http://docalist.org/
 * Description: Docalist-Biblio Extension: provides default thumbails for notices with links.
 * Version:     0.1
 * Author:      Daniel Ménard
 * Author URI:  http://docalist.org/
 * Text Domain: docalist-biblio
 * Domain Path: /languages
 *
 * @package     Docalist\Biblio
 * @subpackage  Thumbnails
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio\Thumbnails;

/**
 * Affiche une erreur dans le back-office si Docalist Core/Biblio n'est pas activé.
 */
add_action('admin_notices', function() {
    if (! function_exists('docalist')) {
        echo '<div class="error"><p>Docalist Biblio Thumbnails requires Docalist Core.</p></div>';
    } elseif (! class_exists('\Docalist\Biblio\Plugin')) {
        echo '<div class="error"><p>Docalist Biblio Thumbnails requires Docalist Biblio.</p></div>';
    }
});

/**
 * Initialise notre plugin une fois que Docalist Core est chargé.
 */
add_action('docalist_loaded', function () {
    docalist('autoloader')->add(__NAMESPACE__, __DIR__ . '/class');
    docalist('services')->add('docalist-biblio-thumbnails', new Plugin());
});