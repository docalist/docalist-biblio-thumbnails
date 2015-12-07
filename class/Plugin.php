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
 */
namespace Docalist\Biblio\Thumbnails;

/**
 * Extension pour Docalist Biblio : génère une image à la une par défaut pour
 * les notices qui ont un lien.
 */
class Plugin
{
    public function __construct()
    {
        // Charge les fichiers de traduction du plugin
        load_plugin_textdomain('docalist-biblio-thumbnails', false, 'docalist-biblio-thumbnails/languages');

        add_filter('post_thumbnail_html', function ($html, $post_id, $post_thumbnail_id, $size, $attr) {
            // Si le post a déjà un thumbnail, terminé
            if ($post_thumbnail_id) {
                return $html;
            }

            // Si ce n'est pas une notice, terminé
            if (substr(get_post_type($post_id), 0, 6) !== 'dclref') {
                return $html;
            }

            // Récupère la notice en cours
            $ref = docalist('docalist-biblio')->getReference($post_id); /* @var $ref \Docalist\Biblio\Reference */

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
                $width = get_option($size . '_size_w');
            } elseif (isset($_wp_additional_image_sizes[$size])) {
                $width = $_wp_additional_image_sizes[$size]['width'];
            } else {
                $width = 150;
            }

            // Construit l'url de l'image thumbnail
            $thumbnail = $this->thumbnailUrl($url, $width);

            // Construit le code html à retourner
            $thumbnail && $html = sprintf('<a href="%s"><img src="%s" width="%s" class="attachment-%s wp-post-image" alt=""%s /></a>', $url, $thumbnail, $width, $size, $attr);

            return $html;
        }, 999, 5);
    }

    protected function thumbnailUrl($url, $width)
    {
        // Liste des fournisseurs de vignettes
        static $providers = [
            'pdf',
            'youtube',
            'dailymotion',
            'vimeo',
            'wordpressMshots',
        ];

//         $url = 'https://vimeo.com/137532049';
//         $url = 'https://vimeo.com/channels/staffpicks/137532049';
//         $url = 'http://www.cnaemo.com/media/partage/pdf/numero_mars_2014_espace_social.pdf';
        // Teste tous les providers dans l'ordre
        foreach ($providers as $provider) {
            if ($result = $this->$provider($url, $width)) {
                return $result;
            }
        }

        // Aucune vignette dispo
        return false;
    }

    protected function youtube($url, $width)
    {
        // Liste des url possibles pour des vidéos youtube
        // inspiré de wp-includes/class-oembed.php::_construct()
        $tests = [
            '~youtube\.com/watch.*v=([a-z0-9]+)~i',
            '~youtu\.be/(.*)~i',
            // youtube.com/playlist :  non géré
        ];

        // Teste s'il s'agit d'une url youtube
        $match = null; // évite warning 'not initialized'
        foreach ($tests as $test) {
            if (preg_match($test, $url, $match)) {
                $id = $match[1];

                // Détermine la taille de la vignette
                // 0 : player background. 480x360
                // 1 : début de la vidéo. 120x90
                // 2 : milieu de la vidéo. 120x90
                // 3 : fin de la vidéo. 120x90
                // *default : Normal Quality. 120x90
                // mqdefault : medium. 320x180 pixels
                // hqdefault : haute qualité. 480x360 pixels

                // pour certaines vidéos (HQ entres autres) :
                // sddefault : standard definition. 640x480
                // maxresdefault : Maximum Resolution. 1920x1080 pixels

                $sizes = [
                    'default' => 120, // 120x90
                    'mqdefault' => 320, // 320x180
                    'hqdefault' => 480, // 480x360
                ];

                foreach ($sizes as $name => $size) {
                    if ($size >= $width) {
                        break;
                    }
                }

                return "http://img.youtube.com/vi/$id/$name.jpg";
            }
        }
    }

    protected function dailymotion($url, $width)
    {
        // Liste des url possibles
        // inspiré de wp-includes/class-oembed.php::_construct()
        $tests = [
            '~dailymotion\.com/video/([a-z0-9]+)~i', // id suivi du titre, l'id s'arrête au signe "_" (exemple : x11fpcv_la-vie-en-vrac_news)
            '~dai\.ly/(.*)~i',
        ];

        // Teste s'il s'agit d'une url dailymotion
        $match = null; // évite warning 'not initialized'
        foreach ($tests as $test) {
            if (preg_match($test, $url, $match)) {
                $id = $match[1];

                // Vignette par défaut quelle que soit la taille demandée
                return "http://www.dailymotion.com/thumbnail/video/$id";

                /*

                // Version utilisant l'API dailymotion (plus lent).
                // cf. https://arjunphp.com/how-to-get-thumbnail-of-dailymotion-video-using-the-video-id/

                // Tailles de vignettes disponibles (http://stackoverflow.com/a/30529975)
                $sizes = [
                    'thumbnail_60_url'  =>  60,
                    'thumbnail_url'     =>  86,
                    'thumbnail_120_url' => 120,
                    'thumbnail_180_url' => 180,
                    'thumbnail_240_url' => 240,
                    'thumbnail_360_url' => 360,
                    'thumbnail_480_url' => 480,
                    'thumbnail_720_url' => 720,
                ];

                foreach ($sizes as $name => $size) {
                    if ($size >= $width) {
                        break;
                    }
                }

                $thumbnail = json_decode(@file_get_contents("https://api.dailymotion.com/video/$id?fields=$name"));
                if (is_null($thumbnail)) {
                    return;
                }

                return $thumbnail->$name;
                */
            }
        }
    }

    protected function vimeo($url, $width)
    {
        // Liste des url possibles
        // inspiré de wp-includes/class-oembed.php::_construct()
        $tests = [
            '~vimeo\.com/(?:\w*/)*([a-z0-9]+)~i',
        ];

        // Teste s'il s'agit d'une url vimeo
        $match = null; // évite warning 'not initialized'
        foreach ($tests as $test) {
            if (preg_match($test, $url, $match)) {
                $id = $match[1];

                $thumbnail = json_decode(@file_get_contents("http://vimeo.com/api/v2/video/$id.json"));
                if (is_null($thumbnail)) {
                    return;
                }

                // Tailles de vignettes disponibles (http://stackoverflow.com/a/30529975)
                $sizes = [
                    'thumbnail_small' => 100, // 100x75
                    'thumbnail_medium' => 200, // 200x150
                    'thumbnail_large' => 640, // 640x360
                ];

                foreach ($sizes as $name => $size) {
                    if ($size >= $width) {
                        break;
                    }
                }

                return $thumbnail[0]->$name;
            }
        }
    }

    protected function pdf($url, $width)
    {
        if (preg_match('~.pdf$~', $url)) {
            return $this->apercite($url, $width);
        }
    }

    protected function apercite($url, $width)
    {
        // Tailles de vignettes disponibles (http://www.apercite.fr/api/doc/#!display)
        $sizes = [
            '80x60' => 80,  // format 4:3
            '100x75' => 100,
            '120x90' => 120,
            '160x120' => 160,
            '180x135' => 180,
            '240x180' => 240,
            '320x240' => 320,
            '560x420' => 560,
            '640x480' => 640,
            '800x600' => 800,
        ];

        foreach ($sizes as $name => $size) {
            if ($size >= $width) {
                break;
            }
        }

        return "http://www.apercite.fr/api/apercite/$name/yes/$url";
    }

    protected function wordpressMshots($url, $width)
    {
        return 'http://s.wordpress.com/mshots/v1/' . urlencode($url) . '?w=' . $width;
    }
}
