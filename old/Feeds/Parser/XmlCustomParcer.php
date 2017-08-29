<?php

namespace Drupal\cmlservice\Feeds\Parser;

/**
 * @file
 * Contains \Drupal\cmlservice\Feeds\Parser\XmlCustomParcer.
 */

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;
use Drupal\cmlservice\Xml\XmlObject;

/**
 * Defines a XmlCustomParcer feed parser.
 *
 * @FeedsParser(
 *   id = "XmlCustomParcer",
 *   title = @Translation("XmlCustomParcer"),
 *   description = @Translation("parce xml string"),
 *   form = {
 *     "configuration" = "Drupal\cmlservice\Feeds\Parser\Form\CustomParcerForm",
 *   },
 * )
 */
class XmlCustomParcer extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $result = new ParserResult();
    $feed_config = $feed->getConfigurationFor($this);
    $xml = $fetcher_result->getRaw();
    $xmlObj = new XmlObject();
    $xmlObj->parseXmlString($xml);
    $xmlObj->find($feed_config['query']);
    $raws = $xmlObj->xmlfind;

    if ($feed_config['limit']) {
      $raws = array_slice($raws, 0, $feed_config['limit']);
      dsm($raws);
    }

    if ($raws) {
      foreach ($raws as $raw) {
        $item = new DynamicItem();
        $item->set('id', $raw['Ид']);
        $item->set('val', $raw['Наименование']);
        $result->addItem($item);
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    $result = [
      'id' => ['label' => "Ид"],
      'val' => ['label' => "Наименование"],
    ];
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return [
      'query' => $this->configuration['query'],
      'limit' => $this->configuration['limit'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'query' => '',
      'limit' => '',
    ];
  }

}
