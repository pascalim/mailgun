<?php

namespace Drupal\mailgun\Plugin\Mail;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Mailgun\Mailgun;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Modify the Drupal mail system to use Mandrill when sending emails.
 *
 * @Mail(
 *   id = "MailgunMailSystem",
 *   label = @Translation("Mailgun Mailer"),
 *   description = @Translation("Sends the message, using Mailgun.")
 * )
 */
class MailgunMailSystem implements MailInterface, ContainerFactoryPluginInterface {

  /**
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * @var Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a MailgunMailSystem object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param $plugin_id
   *   The plugin_id for the plugin instance.
   * @param $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory used by the plugin.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger used by the plugin.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    // Join the body array into one string.
    $message['body'] = implode("\n\n", $message['body']);

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    $params = [
      'from' => $message['from'],
      'to' => $message['to'],
      'subject' => $message['subject'],
      'text' => $message['body'],
    ];

    // Add the CC and BCC fields if not empty.
    if (!empty($message['params']['cc'])) {
      $params['cc'] = $message['params']['cc'];
    }
    if (!empty($message['params']['bcc'])) {
      $params['bcc'] = $message['params']['bcc'];
    }

    // Make sure the files provided in the attachments array exist.
    if (!empty($message['params']['attachments'])) {
      $attachments =& $params['attachment'];
      foreach ($message['params']['attachments'] as $attachment) {
        if (file_exists($attachment)) {
          $attachments[] = $attachment;
        }
      }
    }

    $config = $this->configFactory->get('mailgun.adminsettings');
    $domain = $config->get('working_domain');
    $apiKey = $config->get('api_key');

    $mailgun = Mailgun::create($apiKey);
    return $mailgun->messages()->send($domain, $params);
  }
}
