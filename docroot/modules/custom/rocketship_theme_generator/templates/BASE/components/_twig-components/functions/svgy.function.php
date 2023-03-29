<?php

use Twig\TwigFunction;
$function = new TwigFunction('icon', function($name = '', $title = false, $classes = array()) {
  // if no title added, use the icon name but capitalize
  if (!isset($title) || $title === false || strlen(trim($title)) === 0) {
    $title = ucfirst($name);
  }

  // if $classes overridden with false or is a string, make it an empty array
  // to avoid getting errors or printing blank space
  if (!isset($classes) || $classes === false || is_string($classes)) {
    $classes = array();
  }

  return [
    '#type' => 'inline_template',
    '#template' => '<span class="wrapper--rs-icon"><svg class="rs-icon rs-icon--{{ name }}{% for class in classes %} {{class}}{% endfor %}" role="img" aria-hidden="true" title="{{ title }}" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="#rs-icon--{{ name }}"></use></svg></span>',
    '#context' => [
      'title' => $title,
      'name' => $name,
      'classes' => $classes
    ],
  ];
}, array('needs_context' => false, 'is_safe' => array('html')));
