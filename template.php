<?php
/**
 * @file
 * decoboots main template.php file.
 */

/**
 * Node preprocess.
 */
function decoboots_preprocess_node(&$variables) {
  global $language;
  $variables['language'] = $language;
}

/**
 * Description to be added.
 */
function decoboots_check_term_parent($tid) {
  $check = taxonomy_get_parents($tid);
  if (empty($check)) {
    return TRUE;
  } else {
    return FALSE;
  }
}


function decoboots_preprocess_block(&$variables) {
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

function decoboots_image($variables) {
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
