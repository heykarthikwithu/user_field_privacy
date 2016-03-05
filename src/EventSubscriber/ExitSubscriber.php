<?php /**
 * @file
 * Contains \Drupal\user_field_privacy\EventSubscriber\ExitSubscriber.
 */

namespace Drupal\user_field_privacy\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::TERMINATE => ['onEvent', 0]];
  }

  public function onEvent() {
    foreach (drupal_static('user_field_privacy', []) as $account) {
      $uid = db_select('users', 'u')
        ->fields('u', ['uid'])
        ->condition('mail', $account['mail'])
        ->execute()
        ->fetchField();
      foreach ($account['fields'] as $field_id => $privacy_state) {
        db_merge('user_field_privacy_value')
          ->key([
          'fid' => $field_id,
          'uid' => $uid,
        ])
          ->fields(['private' => $privacy_state ? 1 : 0])
          ->execute();
      }
    }
  }

}
