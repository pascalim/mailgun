<?php

namespace Drupal\mailgun\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;
use Mailgun\Mailgun;

/**
 * Modify the Drupal mail system to use Mandrill when sending emails.
 *
 * @Mail(
 *   id = "mailgun_mail",
 *   label = @Translation("Mailgun mailer"),
 *   description = @Translation("Sends the message using Mailgun.")
 * )
 */
class MailgunMail implements MailInterface {

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    // Concatenate and wrap the e-mail body for either plain-text or HTML e-mails.
    if (is_array($message['body'])) {
      $message['body'] = implode("\n\n", $message['body']);
    }
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

    $config = \Drupal::config('mailgun.adminsettings');
    $domain = $config->get('working_domain');
    $apiKey = $config->get('api_key');

    $mailgun = Mailgun::create($apiKey);
    return $mailgun->messages()->send($domain, $params);
  }
}
