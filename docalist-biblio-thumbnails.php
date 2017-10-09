<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
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

/**
 * Version du plugin.
 */
define('DOCALIST_BIBLIO_THUMBNAILS_VERSION', '0.3.0'); // Garder synchro avec la version indiquée dans l'entête

/**
 * Path absolu du répertoire dans lequel le plugin est installé.
 *
 * Par défaut, on utilise la constante magique __DIR__ qui retourne le path réel du répertoire et résoud les liens
 * symboliques.
 *
 * Si le répertoire du plugin est un lien symbolique, la constante doit être définie manuellement dans le fichier
 * wp_config.php et pointer sur le lien symbolique et non sur le répertoire réel.
 */
!defined('DOCALIST_BIBLIO_THUMBNAILS_DIR') && define('DOCALIST_BIBLIO_THUMBNAILS_DIR', __DIR__);

/**
 * Path absolu du fichier principal du plugin.
 */
define('DOCALIST_BIBLIO_THUMBNAILS', DOCALIST_BIBLIO_THUMBNAILS_DIR . DIRECTORY_SEPARATOR . basename(__FILE__));

/**
 * Url de base du plugin.
 */
define('DOCALIST_BIBLIO_THUMBNAILS_URL', plugins_url('', DOCALIST_BIBLIO_THUMBNAILS));

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