<?php

declare(strict_types=1);

namespace Drupal\akhil_caching\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an userarticlecategoryblock block.
 */
#[Block(
  id: 'akhil_caching_userarticlecategoryblock',
  admin_label: new TranslatableMarkup('UserArticleCategoryBlock'),
  category: new TranslatableMarkup('Custom'),
)]
final class UserArticleCategoryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The view executable.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $articles;

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly AccountProxyInterface $currentUser,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $items = [];
    $articles = $this->getArticles();
    foreach ($articles as $article) {
      $items[] = $article->getTitle();
    }

    $build['content'] = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#type' => 'ul',
    ];
    return $build;
  }

  public function getArticles() {
    if (!$this->articles) {
      $current_user_id = $this->currentUser->getAccount()->id();
      $current_user_category = $this->entityTypeManager->getStorage('user')->load($current_user_id)->get('field_categories')->getString();
      $query = $this->entityTypeManager->getStorage('node')->getQuery()->accessCheck(TRUE);
      $articles = $query->condition('type', 'article')
        ->condition('status', '1')
        ->condition('field_tags', $current_user_category)
        ->sort('created', 'desc')
        ->execute();
      $this->articles = $this->entityTypeManager->getStorage('node')->loadMultiple($articles);
    }

    return $this->articles;
  }

    /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user_category']);
  }

}
