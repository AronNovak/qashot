<?php

namespace Drupal\qa_shot_rest_api\Normalizer;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Class ItemNormalizer.
 *
 * This is only a fallback class.
 *
 * @package Drupal\qa_shot_rest_api\Normalizer
 */
class ItemNormalizer extends NormalizerBase {

  /**
   * ItemNormalizer constructor.
   */
  public function __construct() {
    $this->supportedInterfaceOrClass = [FieldItemInterface::class];
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($fieldItem, $format = NULL, array $context = array()) {
    /** @var \Drupal\Core\Field\FieldItemInterface $fieldItem */
    $value = $fieldItem->getValue();
    if (isset($value['value'])) {
      return $value['value'];
    }
    if (isset($value[0]['value'])) {
      return $value[0]['value'];
    }
    return $value;
  }

}
