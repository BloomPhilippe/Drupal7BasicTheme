<?php
/**
 * @file
 * EXKi main template.php file.
 */

/**
 * Node preprocess.
 */
function exki_preprocess_node(&$variables) {
  global $language;
  if ($variables['type'] == 'restaurant') {
    $restaurant = new ExkiRestaurantDecorator($variables['node']);
    $variables['restaurant'] = $restaurant;
  }

  if ($variables['type'] == 'product') {
    $product = new ExkiProductDecorator($variables['node']);
    $array_url = explode('/', $_SERVER['REQUEST_URI']);
    $source_path = drupal_get_normal_path($array_url[2] . '/' . $array_url[3], $language->language);
    $array_src_path = explode('/', $source_path);
    $product->origin_term = taxonomy_term_load($array_src_path[1]);
    $variables['product'] = $product;
  }
  $variables['language'] = $language;
}

/**
 * Description to be added.
 */
function exki_links__locale_block(&$vars) {
  foreach ($vars['links'] as $language => $lang_info) {
    $abbr = $lang_info['language']->language;
    $name = $lang_info['language']->name;
    $vars['links'][$language]['title'] = '<abbr title="' . $name . '">' . $abbr . '</abbr>';
    $vars['links'][$language]['html'] = TRUE;
  }
  $content = theme_links($vars);
  return $content;
}

/**
 * Description to be added.
 */
function exki_check_term_parent($tid) {
  $check = taxonomy_get_parents($tid);
  if (empty($check)) {
    return TRUE;
  } else {
    return FALSE;
  }
}

function exki_add_language_in_select($array_country, $domaine, $active) {
  global $language;
  $current_domaine = domain_get_domain();
  $menu_domains = '';
  $current_path = current_path();
  foreach ($array_country as $key => $language_item) {
    if ($language->language == $key && $current_domaine['machine_name'] == $domaine['machine_name']) {
      $active = 'selected';
    } else {
      $active = '';
    }
    $url = 'http://' . $domaine['subdomain'] . '/' . $key . '/' . $current_path;
    $menu_domains .= '<option data-country="' . $domaine['machine_name'] . '" data-lang=' . $key . ' value="' . $url . '" ' . $active . '>' . $language_item . '</option>';

  }
  return $menu_domains;
}

/**
 * Description to be added.
 */
function exki_switcher_domains() {

  $exki_countries_languages = variable_get('exki_countries_languages', array(
    'exki_be_local' => array(
      'fr' => 'Belgique',
      'nl' => 'België',
      'en' => 'Belgium',
    ),
    'exki_fr_local' => array(
      'fr' => 'France',
    ),
    'exki_lu_local' => array(
      'en' => 'Luxembourg',
    ),
    'exki_nl_local' => array(
      'nl' => 'Nederlands',
    ),
    'exki_com_local' => array(
      'en' => 'Exki.com',
    ),
  ));

  $languages = language_list();
  $domaines = domain_domains();
  $current_domaine = domain_get_domain();
  $current_path = current_path();
  $menu_domains = '<select style="display: inline-block;">';

  foreach ($domaines as $domaine) {
    $active = '';
    if ($domaine['machine_name'] == 'exki_be_local') {
      $menu_domains .= exki_add_language_in_select($exki_countries_languages['exki_be_local'], $domaine, $active);

    } elseif ($domaine['machine_name'] == 'exki_fr_local') {
      $menu_domains .= exki_add_language_in_select($exki_countries_languages['exki_fr_local'], $domaine, $active);

    } elseif ($domaine['machine_name'] == 'exki_lu_local') {
      $menu_domains .= exki_add_language_in_select($exki_countries_languages['exki_lu_local'], $domaine, $active);

    } elseif ($domaine['machine_name'] == 'exki_nl_local') {
      $menu_domains .= exki_add_language_in_select($exki_countries_languages['exki_nl_local'], $domaine, $active);

    } elseif ($domaine['machine_name'] == 'exki_com_local') {
      $menu_domains .= exki_add_language_in_select($exki_countries_languages['exki_com_local'], $domaine, $active);
    }
  }
  $menu_domains .= '<option data-country="NYC" data-lang="en" value="http://exkinyc.com/">USA</option>';
  $menu_domains .= '</select>';
  return $menu_domains;
}

function exki_preprocess_block(&$variables) {
  if ($variables['block']->module == 'bean') {
    $block = $variables['block'];
    $cta = bean_load_delta($block->delta);
    if ($variables['block']->region != 'footer_cta_big') {
      $variables['theme_hook_suggestions'][] = 'block__bean_' . $cta->type;
    }else {
      $variables['theme_hook_suggestions'][] = 'block__bean_' . $cta->type . '_big';
    }
    if ($cta->type == 'cta' || $cta->type == 'tips') {
      $new_cta = new stdClass();
      $new_cta->url = $cta->field_link['und'][0]['url'];
      $new_cta->type = $cta->label;
      $new_cta->title = $cta->title;
      $new_cta->picture_url = file_create_url($cta->field_main_image['und'][0]['uri']);
      $img = '';
      if (!empty($cta->field_main_image)) {
        $img = theme('image_style', array(
                'path' => $cta->field_main_image['und'][0]['uri'],
                'style_name' => 'medium_picture',
                'alt' => $cta->field_main_image['und'][0]['alt'],
                'title' => $cta->field_main_image['und'][0]['title'],
            ));
      }
      $new_cta->picture = $img;
      $variables['cta'] = $new_cta;
    }
  }
}

function exki_image($variables) {
  $attributes = $variables['attributes'];

  $attributes['src'] = file_create_url($variables['path']);

  preg_match('/styles\/([a-z_]*)\//', $variables['path'], $m);
  if (isset($m[1])) {
    $style = $m[1];
    $style_retina = $m[1] . "_retina";
    $path_retina = str_replace($style, $style_retina, $variables['path']);
    $attributes['srcset'] = file_create_url($variables['path']) . " 1x, " . $path_retina . " 2x";
  }

  foreach (array('width', 'height', 'alt', 'title') as $key) {
    if (isset($variables[$key])) {
      $attributes[$key] = $variables[$key];
    }
  }

  return '<img' . drupal_attributes($attributes) . ' />';
}

function truncate_text($text, $nbr) {
  return substr($text, 0, $nbr);
}

?>
