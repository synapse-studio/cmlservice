<?php

namespace Drupal\cmlservice\Protocol;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\SimpleXMLElement;

/**
 * Controller routines for page example routes.
 */
class CmlQuery extends ControllerBase {

  /**
   * Main.
   */
  public static function main($type) {
    if (CmlCheckAuth::auth()) {
      $cml_id = CmlCheckAuth::check();
      if ($type == 'sale') {
        $result  = "success\n";

        $filepath = 'public://cml-files/sale/' . $cml_id . '/';
        file_prepare_directory($filepath, FILE_CREATE_DIRECTORY);
        $file = file_save_data('', $filepath . 'export.xml', FILE_EXISTS_REPLACE);
        $file->save();
        self::makeXmlExportOrders($file->getFileUri());

        $str = file_get_contents(file_create_url($file->getFileUri()));
        header("Cache-Control: private");
        header('Content-Type: application/xml');
        header('Content-Length: ' . strlen($str));
        header('Content-Disposition: attachment; filename=export.xml');
        header('Cache-Control: public');
        header('Pragma: no-cache');
        header("Expires: 0");
        die($str);
      }
      else {
        $result  = "failure\n";
        $result .= "unknown type\n";
      }
      Cml::debug(__CLASS__, "sale mode query " . $cml_id);
    }
    else {
      $result .= "failure\n";
      $result .= "auth error\n";
      Cml::debug(__CLASS__, "Ошибка авторизации. Base.");
    }
    return $result;
  }

  /**
   * Формируем данные из профиля.
   */
  public static function makeCounterparty(&$element, $order) {
    $profile = $order->getBillingProfile();
    $name = isset($profile->field_customer_fie->value) ? $profile->field_customer_fie->value : '';
    $phone = isset($profile->field_customer_phone->value) ? $profile->field_customer_phone->value : '';
    $city = isset($profile->field_city->value) ? $profile->field_city->value : '';
    $street = isset($profile->field_street->value) ? $profile->field_street->value : '';
    $house = isset($profile->field_house->value) ? $profile->field_house->value : '';
    $apartment = isset($profile->field_apartment->value) ? $profile->field_apartment->value : '';
    $postcode = isset($profile->field_postcode->value) ? $profile->field_postcode->value : '';
    $email = isset($profile->field_customer_email->value) ? $profile->field_customer_email->value : '';

    $element->addChild('Наименование', $name);
    $element->addChild('Роль', 'Покупатель');
    $element->addChild('ПолноеНаименование', $name);
    $element->addChild('Телефон', $phone);
    $element->addChild('Город', $city);
    $element->addChild('Улица', $street);
    $element->addChild('Дом', $house);
    $element->addChild('Квартира', $apartment);
    $element->addChild('Индекс', $postcode);
    $element->addChild('ЭлектроннаяПочта', $email);
  }

  /**
   * Формируем данные по заказам в хмл.
   */
  public static function makeXmlExportOrders($fileUri) {
    $sales = new SimpleXMLElement('<КоммерческаяИнформация/>');
    $sales->addAttribute('ВерсияСхемы', '2.09');
    $date = format_date(time(), 'custom', 'Y-m-d');
    $sales->addAttribute('ДатаФормирования', $date);

    $query = \Drupal::entityQuery('commerce_order')
      ->condition('cart', 0);
    $result = $query->execute();

    foreach ($result as $key => $orderId) {
      $order = \Drupal::entityManager()->getStorage('commerce_order')->load($orderId);
      $query = \Drupal::entityQuery('commerce_payment')
        ->condition('order_id', $orderId);
      $paymentsIds = $query->execute();

      $document = $sales->addChild('Документ');
      $document->addChild('Ид', $orderId);
      $document->addChild('Номер', $orderId);
      $document->addChild('Дата', format_date($order->getCompletedTime(), 'custom', 'Y-m-d'));
      $document->addChild('Время', format_date($order->getCompletedTime(), 'custom', 'H:i:s'));
      $document->addChild('Валюта', $order->total_price->currency_code);
      $document->addChild('Сумма', $order->total_price->number);
      $document->addChild('ХозОперация', 'Заказ товара');

      $contragents = $document->addChild('Контрагенты');
      $contragent = $contragents->addChild('Контрагент');
      self::makeCounterparty($contragent, $order);

      $theGoods = $document->addChild('Товары');

      $orderItemsObj = $order->order_items;
      for ($orderItemPlace = 0; $orderItemPlace < $orderItemsObj->count(); $orderItemPlace++) {
        foreach ($orderItemsObj->get($orderItemPlace)->getValue() as $key => $itemId) {
          $orderItem = \Drupal::entityManager()->getStorage('commerce_order_item')->load($itemId);
          $goods = $theGoods->addChild('Товар');
          $goods->addChild('ЦенаЗаЕдиницу', $orderItem->getUnitPrice());
          $goods->addChild('Сумма', $orderItem->getTotalPrice());
          $goods->addChild('Количество', $orderItem->getQuantity());
          // $offer = \Drupal::entityManager()->getStorage('commerce_product_variation')->load($itemId);
          $offer = $orderItem->getPurchasedEntity();
          $goods->addChild('Ид', $offer->getSku());
        }
      }
    }

    $sales->asXML($fileUri);
  }

}
