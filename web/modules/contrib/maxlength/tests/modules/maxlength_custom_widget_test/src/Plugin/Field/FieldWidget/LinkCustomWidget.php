<?php

namespace Drupal\maxlength_custom_widget_test\Plugin\Field\FieldWidget;

use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * Plugin implementation of the 'maxlength_link_custom_widget' widget.
 *
 * @FieldWidget(
 *   id = "maxlength_link_custom_widget",
 *   label = @Translation("Link custom widget for testing purpose"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkCustomWidget extends LinkWidget {

}
