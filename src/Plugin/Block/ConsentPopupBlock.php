<?php

namespace Drupal\consent_popup\Plugin\Block;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an consent popup block.
 *
 * @Block(
 *   id = "consent_popup",
 *   admin_label = @Translation("Consent Popup"),
 *   category = @Translation("Custom")
 * )
 */
class ConsentPopupBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::blockForm($form, $form_state);
    $languageManager = \Drupal::service('language_manager');
    $languages = $languageManager->getLanguages();
    foreach ($languages as $key => $language) {
      $form[$key] = [
        '#type' => 'details',
        '#title' => $language->getName(),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      $form[$key]['text'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Popup Text'),
        '#default_value' => isset($config[$key]['text'])  ? 
                            $config[$key]['text'] :  $this->t("Are you an adult?"),
        '#required' => true
      ];
      $form[$key]['text_decline'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Declined Text'),
        '#default_value' => isset($config[$key]['text_decline'])  ? 
                            $config[$key]['text_decline'] : 
                            $this->t("You can't access this page"),
        '#required' => true
      ];
      $form[$key]['accept'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Accept button text'),
        '#default_value' => isset($config[$key]['accept'])  ? $config[$key]['accept'] : $this->t('Yes'),
        '#description' => $this->t('Default value: Yes'),
        '#required' => true
      ];
      $form[$key]['decline'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Decline button text'),
        '#default_value' => isset($config[$key]['decline'])  ? $config[$key]['decline'] : $this->t('No'),
        '#description' => $this->t('Default value: No'),
        '#required' => true
      ];
    }
    $form['decline_link'] =[
      '#type' => 'fieldset',
      '#title' => $this->t('Decline url options'),
      '#description' => $this->t('Options for the link to show when declined')
    ];
    $form['decline_link']['decline_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link url if declined'),
      '#default_value' => isset($config['decline_link']['decline_url'])  ? $config['decline_link']['decline_url'] : '/',
      '#description' => $this->t("Please use a relative url. Default value '/' (home) "),
      '#required' => true
    ];
    $form['decline_link']['decline_url_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for url if declined'),
      '#default_value' => isset($config['decline_link']['decline_url_text'])  ? $config['decline_link']['decline_url_text'] : $this->t('Keep browsing our site'),
      '#description' => $this->t("Link text. Default value 'Keep browsing our site'"),
      '#required' => true
    ];
    $form['cookie'] =[
      '#type' => 'fieldset',
      '#title' => $this->t('Cookie Info'),
      '#description' => $this->t('Options for the link to show when declined')
    ];
    $form['cookie']['cookie_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie Name'),
      '#default_value' => isset($config['cookie']['cookie_name'])  ? $config['cookie']['cookie_name'] : 'consent_popup',
      '#description' => $this->t("Name of the cookie (defaults to consent_popup)"),
      '#required' => true
    ];
    $form['cookie']['cookie_life'] = [
      '#type' => 'number',
      '#title' => $this->t('Cookie life time'),
      '#default_value' => isset($config['cookie']['cookie_life'])  ? $config['cookie']['cookie_life'] : 7,
      '#description' => $this->t("how many days until the cookie is deleted (defaults to 7)")
    ];
    $form['design'] =[
      '#type' => 'fieldset',
      '#title' => $this->t('Cookie Info'),
      '#description' => $this->t('Options for the link to show when declined')
    ];
    $form['design']['color'] = array(
      '#type' => 'color',
      '#title' => $this
        ->t('Background Color'),
      '#default_value' => isset($config['design']['color']) ? $config['design']['color'] : '#000000',
    );
    $form['design']['color_opacity'] = [
      '#type' => 'select',
      '#title' => t('Background Opacity'),
      '#description' => $this->t('Chose the filter opacity'),
      '#options' => range(0, 1, 0.1),
      '#default_value' => isset($config['design']['color_opacity']) ? $config['design']['color_opacity'] : '8',
    ];
    $form['design']['blur'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Elements to blur'),
      '#default_value' => isset($config['design']['blur'])  ? $config['design']['blur'] : '',
      '#description' => $this->t("Use css selectors to chose elements to blur separated with , ")
    ];
    return $form;
  }
  

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $languageManager = \Drupal::service('language_manager');
    $language = $languageManager->getCurrentLanguage();
    $languageKey = $language->getId();
    $config = $this->getConfiguration();
    list($r, $g, $b) = sscanf($config['design']['color'], "#%02x%02x%02x");
    $opacity = $config['design']['color_opacity'] / 10;
    $color = "rgb(".$r." ".$g." ".$b." / ".$opacity.")";
    $blurElements = explode(',',$config['design']['blur']);
    $build =  [
      '#theme' => 'consent_popup',
      '#items' => [
        'text' => $config[$languageKey]['text'],
        'accept' => $config[$languageKey]['accept'],
        'decline' => $config[$languageKey]['decline'],
        'url' => $config['decline_link']['decline_url'],
        'url_text' => $config['decline_link']['decline_url_text']
      ],
      '#attached' =>[
        'library' => [
          'consent_popup/consent_popup'
        ],
        'drupalSettings' => [
          'consent_popup' => [
            'cookie_life' => $config['cookie']['cookie_life'],
            'cookie_name' => $config['cookie']['cookie_name'],
            'text_decline' => $config[$languageKey]['text_decline'],
            'bg_color' => $color,
            'to_blur' => $blurElements
          ]
        ]
      ]
    ];
    return $build;
  }
}
