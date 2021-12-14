<?php

namespace Drupal\fg_api_key_verification\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Implementing a ajax form.
 */
class SignatureGenratorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
   return 'signatureGenerator';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['field-group'] = array(
      '#type' => 'details',
      '#title' => $this->t('Signature generator'),
      '#description' => $this->t('Paset the "Data" and "Body" field of request object'.
                              ' and click Submit to Genrate the signature.'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );
    $form['field-group']['request_data'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Input the "Request Object" : '),
    ];

    $form['field-group']['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::signatureGenerate',
      ],
    ];
    $form['field-group']['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="signature_generation"></div>',
    ];
    return $form;

  }
  /**
   * Setting the message in our form.
   */
  public function signatureGenerate(array $form, FormStateInterface $form_state) {
      /*
       Sample data from DANA website
      $datavar1 = '{"head":{"version":"2.0","function":"dana.oauth.auth.applyToken",
      "clientId":2.0171219642091913e+21,"clientSecret":"a203c10d9b78c9b05825e1f3b2a23800",
      "reqTime":"2018-10-15T06:13:23+0000","reqMsgId":2021001649},
      "body":{"grantType":"AUTHORIZATION_CODE","authCode":123123123123}}';*/

    $response_object = strval($form_state->getValue('request_data'));

    //Private key from DANA https://developers.dana.id/?p=311
    $privateKeyContent = <<<EOD
        -----BEGIN RSA PRIVATE KEY-----
        MIIEpAIBAAKCAQEAs8YeM+SmYHdl85o8KpZh2vOw6MVFduZpNW3Jshqp2fhfFnMp
        OWMC5w491RE7j44kqp+pf1TGtJR2TA8Ys3sfm36XOZC1+Phju/rC8Xxgoo1U1A1k
        BE6sQUQTdvBs/es6pLVbw91NisjNgWBXj8AmIFy+Gs0ldw4nORmBvipSu/xM9qqg
        ZFzbWnB2YkM9qOr/PWWsqKUIHh9vLAourLoIdB5AI042rmcJSkejpHzwGKakNNAo
        lw8TCLw6reLZQTikseqhKEPLt0A8BVqz/L3i4DCrNo2/6inXHe/36URmvJRpQ3eb
        xwnDIG39SLoPSIYPQyKPmoOOjf+AP0yjprGyeQIDAQABAoIBAEhav8MxDsmapJz0
        Aa0+U2o1VImLBFdDiyqm4lvdoWkKLvMxLHFdaUinkblUsz0m/5jwo96Mt2Dss+QO
        22k5b9I8lA7mMGdhSXraBWX+IkKqUW77aLrXEzs9c/wV7jgQWcz69VESm+f0w8mD
        hhpkQrQZv6W31ZFiT+UKYA6yqBPiXUTO5OcYWFLkcFanW2imqncSxg9zUB1BPPOc
        Pne4OrtTsLRqIySpR4mVoQiSKNZbcT+X8vUNwcHMwFYaY+6hyf07rA37jJtVs4Jt
        487Q+ZJgTX733t9UKRmTMy7Zpf8j28BE32El3MKYqTPNPm4s8Nmn35b6Wp8aN8M4
        zldw54ECgYEA7tcLqpENZfWjqeZaf/dRKE5l9pvjkCx1ewsoUZ4B8KppCsF+4kfz
        IJNBJyYnjMnyV02pchPTMxkswmFw8a7+c4stCJsldzSSM0R7D8RaZNmOUyS4fzie
        +6y5rmmqCDhbKWKfjD/VsX06WwXXfMkJO7J5Tr4yo6dW9Bt60JiVAnECgYEAwLCt
        OGed2GCIK2H9KR0CmUwF5uKmZQXKHYr/3zZjXDStTF0ORg67JRp5p5LtNLdk3YXr
        nHuPRnpX08/+s2n9x4bnvmffdl02wYse/wbukThHU+nBeX9Ye1xwhaH9r7HZqW7q
        aEY7oVxWmrwt2uCCVr9dyBZ2OqfgJDND4IsTpIkCgYEA1kbue4eJerknrW0yMm6D
        TGMRzW9MeXO5rrty386ftPheJz00BfVBJi+Wm3X6s7AWkMbnR6aLq+NhKb9cIii6
        tpTdwUPYGBt9Myu0MJAb/TDGJMfkEpeM4wSyzcyUtK1C9F08AQrgQE22hiU2kAiZ
        FQrpFIFFU1f1hioFRJIv1bECgYEAumIrK4QOil3VWSFPX5VQDjga/Vn+2XjgJ/Nr
        zN0u/uF1P4hDZkCZhSo2woC8MWGzjxMa9CIQVHvCuH9YPvMnJvi28NZIsJU5gbyw
        Hr71xEJLvD8/heZIEAs6TAiE+o1tnRZMZtCInWxT2RhswmqnqIEylXgkpFf91wY/
        gdMFDQECgYB998Jxo9Q9XFk1P49Zlw7nwRCpqiehfHh9kkA16uq644TDdSVh/178
        EcJ2k6zl94z29naLyW2emq5JWY+4xdGSSI7yqnwhXcUEJBsd6fd8YRb+DMAXO5T7
        zufXOKPk4Hu7CenKShdMWk9ZN43U2qczHWzYojOyG0Khq85aMRakHA==
        -----END RSA PRIVATE KEY-----
        EOD;

    $signature = '';

    $value = openssl_sign($response_object, $signature, $privateKeyContent, OPENSSL_ALGO_SHA256);
    $signature_result = base64_encode($signature);

    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.signature_generation',
        '<div class="my_top_message">'
         //. $this->t('The results is @result', ['@result' => ($form_state->getValue('number_1') + $form_state->getValue('number_2'))])
         .'<div class="signature_result" style="overflow-wrap: break-word;">Copy this Signature in ResponseObject - <br/>'
         . '<pre>' . $signature_result .'</pre>'
         .'</div></div>'),
    );
    return $response;
  }


  /**
   * Submitting the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
