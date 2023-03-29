<?php
use Twig\TwigFunction;
/**
 * @file
 * Add "bem" function for Pattern Lab & Drupal
 * Modified 23/05/2018: changed order & names of the bem variables to better match BEM naming
 * Modified 08/2020: added some default values for the vars for easier conditional checks
 */

use Drupal\Core\Template\Attribute;

$function = new TwigFunction('bem', function ($context, $block = false, $element = false, $modifiers = array(), $extra = array(), $myAttr = array()) {
  $classes = [];

  // If using a blockname to override default class.
  if ($block) {
    // Set blockname class.

    if ($element) {
      $classes[] = $block . '__' . $element;
    } else {
      $classes[] = $block;
    }

    // Set blockname--modifier classes for each modifier.
    if (isset($modifiers) && is_array($modifiers)) {
      foreach ($modifiers as $modifier) {

        if ($element) {
          $classes[] = $block . '__' . $element . '--' . $modifier;
        } else {
          $classes[] = $block . '--' . $modifier;
        }

      };
    }
  }
  // If not overriding base class.
  else {
    // Set base class.
    $classes[] = $element;
    // Set base--modifier class for each modifier.
    if (isset($modifiers) && is_array($modifiers)) {
      foreach ($modifiers as $modifier) {
        $classes[] = $element . '--' . $modifier;
      };
    }
  }

  // If extra non-BEM classes are added.
  if (isset($extra) && is_array($extra)) {
    foreach ($extra as $extra_class) {
      $classes[] = $extra_class;
    };
  }

  // Merge any custom or implicit (from Drupal's context) attributes to $attributes object
  //  split off the classes and put those into its own object

  if ( isset($myAttr) || (class_exists('Drupal') && isset($context['attributes'])) ) {

    $attributes = new Attribute();

    if (isset($myAttr)) {

      // Iterate the attributes available in context.
      foreach($myAttr as $key => $value) {

        // If there are classes, add them to the classes array.
        if ($key === 'class') {
          foreach ($value as $class) {
            $classes[] = $class;
          }
        }
        // Otherwise add the attribute straightaway.
        else {
          $attributes->setAttribute($key, $value);
        }
      }

      // Add class attribute.
      if (!empty($classes)) {
        $attributes->setAttribute('class', $classes);
      }

    }

    if (class_exists('Drupal') && isset($context['attributes'])  ) {


      // Iterate the attributes available in context.
      foreach($context['attributes'] as $key => $value) {

        // If there are classes, add them to the classes array.
        if ($key === 'class') {
          foreach ($value as $class) {
            $classes[] = $class;
          }
        }
        // Otherwise add the attribute straightaway.
        else {
          $attributes->setAttribute($key, $value);
        }

        // Remove the attribute from context so it doesn't trickle down to
        // includes.
        $context['attributes']->removeAttribute($key);
      }

      // Add class attribute.
      if (!empty($classes)) {
        $attributes->setAttribute('class', $classes);
      }

    }
    else {
      $attributes = 'class="' . implode(' ', $classes) . '"';

    }

    return $attributes;

  }

}, array('needs_context' => true, 'is_safe' => array('html')));
