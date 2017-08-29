<?php

namespace Drupal\cmlservice\Feeds\Fetcher;

use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\Fetcher\FetcherInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResult;
use Drupal\feeds\StateInterface;
use Drupal\cmlservice\Controller\GetLastCml;

/**
 * Defines a CML fetcher.
 *
 * @FeedsFetcher(
 *   id = "commerceml",
 *   title = @Translation("CommerceML"),
 *   description = @Translation("import.xml & offers.xml from entity"),
 *   form = {
 *     "configuration" = "Drupal\cmlservice\Feeds\Fetcher\Form\CommerceMLFetcherForm",
 *     "feed" = "\Drupal\cmlservice\Feeds\Fetcher\Form\CommerceMLFeedForm",
 *   },
 * )
 */
class CommerceML extends PluginBase implements FetcherInterface {

  /**
   * {@inheritdoc}
   */
  public function fetch(FeedInterface $feed, StateInterface $state) {
    $target = $feed->getSource();
    $filekeys = $this->configuration['file-name'];


    if (!$target) {
      $target = $this->configuration['target-id'];
    }
    $cml = GetLastCml::load($target);

    $cml_xml = $cml->field_cml_xml->getValue();
    $files = [];

    if (!empty($cml_xml)) {
      foreach ($cml_xml as $xml) {
        $file = file_load($xml['target_id']);
        $filename = $file->getFilename();
        $filekey = strstr($filename, '.', TRUE);
        if (isset($filekeys[$filekey]) && $filekeys[$filekey]) {
          $files[] = $file->getFileUri();
        }
      }
      dsm($files);
    }
    else {
      throw new \RuntimeException('nid:' . $target . ' node->field_cml_xml empty');
    }
    if (!empty($files)) {
      $state->files = $files;
      $state->total = count($state->files);
      if ($state->files) {
        $file = array_shift($state->files);
        $state->progress($state->total, $state->total - count($state->files));
        return new FetcherResult($file);
      }
    }
    else {
      throw new \RuntimeException('nid:' . $target . ' files is empty');
    }

    throw new EmptyFeedException();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return ['source' => ''];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'target-id' => FALSE,
      'file-name' => ['import'],
    ];
  }

}
