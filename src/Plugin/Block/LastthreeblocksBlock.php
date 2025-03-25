<?php

declare(strict_types=1);

namespace Drupal\akhil_caching\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a lastthreeblocks block.
 */
#[Block(
  id: 'akhil_caching_lastthreeblocks',
  admin_label: new TranslatableMarkup('lastthreeblocks'),
  category: new TranslatableMarkup('Custom'),
)]
final class LastthreeblocksBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AccountInterface $currentUser
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
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $items = [];
    $last_3_articles = $this->getLastThreeArticles();
    $items[] = $this->currentUser->getEmail();
    foreach ($last_3_articles as $article) {
      $items[] = $article->getTitle();
    }

    $build['content'] = [
      '#theme' => 'item_list',
      '#title' => 'Last 2 articles',
      '#items' => $items,
      '#type' => 'ul',
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags()
  {
    $tags = [];
    $last_3_articles = $this->getLastThreeArticles();
    foreach ($last_3_articles as $article) {
      $tags = array_merge($tags, $article->getCacheTagsToInvalidate());
    }
    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }

  public function getLastThreeArticles() {
    if (!$this->articles) {
      $query = $this->entityTypeManager->getStorage('node')->getQuery()->accessCheck(TRUE);
      $last_3_articles = $query->condition('type', 'article')
        ->condition('status', '1')
        ->sort('created', 'desc')
        ->range(0, 3)
        ->execute();
      $this->articles = $this->entityTypeManager->getStorage('node')->loadMultiple($last_3_articles);
    }

    return $this->articles;
  }

}
