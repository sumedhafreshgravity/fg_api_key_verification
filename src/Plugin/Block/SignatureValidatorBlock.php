<?php

namespace Drupal\fg_api_key_verification\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "signature_validator_block",
 *   admin_label = @Translation("SignatureValidatorBlock"),
 * )
 */
class SignatureValidatorBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {

    $form = \Drupal::formBuilder()->getForm('\Drupal\fg_api_key_verification\Form\SignatureValidatorForm');
    return $form;
  }



  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['signature_validator_block'] = $form_state->getValue('signature_validator_block');
  }
}