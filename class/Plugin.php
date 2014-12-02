<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2014-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio
 * @subpackage  Thumbnails
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Thumbnails;

/**
 * Extension pour Docalist Biblio : génère une image à la une par défaut pour
 * les notices qui ont un lien.
 */
class Plugin {

    public function __construct() {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-biblio-thumbnails', false, 'docalist-biblio-thumbnails/languages');

        // Si docalist-core n'est pas chargé, inutile d'aller plus loin
        if (! function_exists('docalist')) {
            return;
        }

        add_filter( 'post_thumbnail_html', function($html, $post_id, $post_thumbnail_id, $size, $attr) {
            // Si le post a déjà un thumbnail, terminé
            if ($post_thumbnail_id) {
                return $html;
            }

            // Si ce n'est pas une notice, terminé
            if (substr(get_post_type($post_id), 0, 6) !== 'dclref') {
                return $html;
            }

            // Récupère la notice en cours
            $ref = docalist('docalist-biblio')->getReference($post_id, 'base'); /* @var $ref \Docalist\Biblio\Reference */

            // Si la notice n'a pas de liens, terminé
            if (! isset($ref->link)) {
                return $html;
            }

            // Récupère le premier lien qui figure dans la notice
            $url = $ref->link->first()->url->value();

            // Récupère la largeur de l'image à générer
            // source : http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
            global $_wp_additional_image_sizes;

            if (in_array($size, ['thumbnail', 'medium', 'large'])) {
                $width = get_option($size . '_size_w' );
            } elseif (isset($_wp_additional_image_sizes[$size])) {
                $width = $_wp_additional_image_sizes[$size]['width'];
            } else {
                $width = 150;
            }

            // Construit l'url de l'image thumbnail
            $url = 'http://s.wordpress.com/mshots/v1/' . urlencode($url) . '?w=' . $width;

            // Construit le code html à retourner
            $html = sprintf('<img src="%s" width="%s" class="attachment-%s wp-post-image" alt=""%s />', $url, $width, $size, $attr);

            return $html;
        }, 999, 5);
    }
}