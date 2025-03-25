<?php

declare(strict_types=1);

namespace Drupal\akhil_caching\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Cache\Context\UserCacheContextBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * @todo Add a description for the cache context.
 *
 * Cache context ID: 'user_category'.
 *
 * @DCG
 * Check out the core/lib/Drupal/Core/Cache/Context directory for examples of
 * cache contexts provided by Drupal core.
 */
final class UserCategoryCacheContext extends UserCacheContextBase implements CalculatedCacheContextInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    AccountInterface $user
  ) {
    parent::__construct($user);
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel(): string {
    return (string) t('User category');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = NULL): string {
    // @todo Calculate the cache context here.
    $user_id = $this->user->id();
    $user_category = $this->entityTypeManager->getStorage('user')->load($user_id)->get('field_categories')->getString();
    $context = 'user_' . $user_id . '_' . $user_category;
    return $context;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = NULL): CacheableMetadata {
    return (new CacheableMetadata())->setCacheTags(['user:' . $this->user->id()]);
  }

}
