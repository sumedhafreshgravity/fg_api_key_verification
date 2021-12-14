<?php

namespace Drupal\fg_api_key_verification\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Implementing a ajax form.
 */
class SignatureValidatorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'signature_vaidator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['field-group'] = array(
      '#type' => 'details',
      '#title' => $this->t('Signature Validator'),
      '#description' => $this->t('Paset the "Data" and "Body" field of response object'.
                          ' along with "Signature". Click Submit to Validate the signature.'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );
    $form['field-group']['#attributes']['class'][] = 'signature-generator';
    $form['field-group']['request_data'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Input the "Request Object" : '),
    ];
    $form['field-group']['signature'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Input the "Signature" : '),
    ];
    $form['field-group']['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::verifySignature',
      ],
    ];
    $form['field-group']['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="signature_varification"></div>',
    ];

    return $form;
  }

  /**
   * Setting the message in our form.
   */
  public function verifySignature(array $form, FormStateInterface $form_state) {
    $publicKeyContent = <<< EOD
                        -----BEGIN PUBLIC KEY-----
                        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs8YeM+SmYHdl85o8KpZh
                        2vOw6MVFduZpNW3Jshqp2fhfFnMpOWMC5w491RE7j44kqp+pf1TGtJR2TA8Ys3sf
                        m36XOZC1+Phju/rC8Xxgoo1U1A1kBE6sQUQTdvBs/es6pLVbw91NisjNgWBXj8Am
                        IFy+Gs0ldw4nORmBvipSu/xM9qqgZFzbWnB2YkM9qOr/PWWsqKUIHh9vLAourLoI
                        dB5AI042rmcJSkejpHzwGKakNNAolw8TCLw6reLZQTikseqhKEPLt0A8BVqz/L3i
                        4DCrNo2/6inXHe/36URmvJRpQ3ebxwnDIG39SLoPSIYPQyKPmoOOjf+AP0yjprGy
                        eQIDAQAB
                        -----END PUBLIC KEY-----
                        EOD;
    $publicKeyContent = <<< EOD
                        -----BEGIN PUBLIC KEY-----
                        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs8YeM+SmYHdl85o8KpZh
                        2vOw6MVFduZpNW3Jshqp2fhfFnMpOWMC5w491RE7j44kqp+pf1TGtJR2TA8Ys3sf
                        m36XOZC1+Phju/rC8Xxgoo1U1A1kBE6sQUQTdvBs/es6pLVbw91NisjNgWBXj8Am
                        IFy+Gs0ldw4nORmBvipSu/xM9qqgZFzbWnB2YkM9qOr/PWWsqKUIHh9vLAourLoI
                        dB5AI042rmcJSkejpHzwGKakNNAolw8TCLw6reLZQTikseqhKEPLt0A8BVqz/L3i
                        4DCrNo2/6inXHe/36URmvJRpQ3ebxwnDIG39SLoPSIYPQyKPmoOOjf+AP0yjprGy
                        eQIDAQAB
                        -----END PUBLIC KEY-----
                        EOD;
    $signature = strval($form_state->getValue('signature'));
    //$signature = "oB8bIPccmSKAt8t9PyByn3JJKQSkNvpG7wbiVHubIqfexGEfGNibdRVTslPmt0nc8GjI1pcU9IOOgQMD2Xxos8op5J4xNvzVfIxCTDjdcgnLQ8pGK7YHucnUEVDxW2vZucNyv604uVYNNoc0S6GPhSZrTCP0uwLUPRsfdK+54cWLuQIalpQSs/K23u8iVWZtMsNbtX5nFaSxJ09aXtdMK83c+KLKEJcRHs1nVS1TbahywVY+6utGW0ZlpPkcr5UA4ds38Q50CnhEVswkUGoWLknjlg3sxELU6fhCv5xDZR7Cf8AbdvUJRuBSg3NDDEfaiI2R+5pOMckOAXfw8An2Kg==";
    $response_object = strval($form_state->getValue('request_data'));
    //$response_object = '{head:{version:2.0,  "function":  "dana.oauth.auth.applyToken",  "clientId":2.0171219642091913e+21,  "clientSecret  ":  "a203c10d9b78c9b05825e1f3b2a23800",  "reqTime":  "2018-10-15T06:13:23+0000",  "reqMsgId":2021001649},  "body":{  "grantType":  "AUTHORIZATION_CODE",  "authCode":123123123123}}';
    $isValid = $this->verify($response_object, $publicKeyContent, $signature);

    if ($isValid == 1) {
        $result = "Signature is Valid";
    } else {
      $result = "Signature is Invalid";
    }
    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.signature_varification',
        '<div class="signature_message"><pre>'
        . $result
        . '</pre></div>'),
    );
    return $response;
  }

   /**
 * @param $data string data in json
 * @param $publicKey string of public key in PKCS#8 format
 * @param $signature string of signature in base64 encoded
 *
 * @return string base 64 signature
 */
function verify($data, $publicKey, $signature) {
  $binarySignature = base64_decode($signature);

  return openssl_verify($data, $binarySignature,  $publicKey, OPENSSL_ALGO_SHA256);
}


  /**
   * Submitting the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

