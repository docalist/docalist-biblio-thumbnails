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
 * Version:     0.3.0
 * Author:      Daniel Ménard
 * Author URI:  http://docalist.org/
 * Text Domain: docalist-biblio
 * Domain Path: /languages
 *
 * @package     Docalist\Biblio
 * @subpackage  Thumbnails
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */

namespace Docalist\Biblio\Thumbnails;

// Définit une constante pour indiquer que ce plugin est activé
define('DOCALIST_BIBLIO_THUMBNAILS', __DIR__);

/**
 * Initialise le plugin.
*/
add_action('plugins_loaded', function () {
    // Auto désactivation si les plugins dont on a besoin ne sont pas activés
    $dependencies = ['DOCALIST_CORE', 'DOCALIST_BIBLIO'];
    foreach($dependencies as $dependency) {
        if (! defined($dependency)) {
            return add_action('admin_notices', function() use ($dependency) {
                deactivate_plugins(plugin_basename(__FILE__));
                unset($_GET['activate']); // empêche wp d'afficher "extension activée"
                $dependency = ucwords(strtolower(strtr($dependency, '_', ' ')));
                $plugin = get_plugin_data(__FILE__, true, false)['Name'];
                echo "<div class='error'><p><b>$plugin</b> has been deactivated because it requires <b>$dependency</b>.</p></div>";
            });
        }
    }

    // Ok
    docalist('autoloader')->add(__NAMESPACE__, __DIR__ . '/class');
    docalist('services')->add('docalist-biblio-thumbnails', new Plugin());
});