# Выгрузка товаров из 1с в Drupal 8
Модуль разделён на 2 и выложен на drupal.org:
 * **Commerce ML API** https://www.drupal.org/project/cmlapi
 * **Commerce ML Exchange** https://www.drupal.org/project/cmlexchange
 * Рекомендуем к ним добавить **Commerce ML Migrations** https://www.drupal.org/project/cmlmigrations

## Commerce ML API 
 * хранение и управление информацей об обменах
 * парсинг XML
 
## Commerce ML Exchange 
 * обеспечение протокола обмена

## Commerce ML Migrations
 * загрузка информации из import.xml и offers.xml в каталог-таксономию и drupal_commerce

## Модуль служит для интеграции 1С и Друпал8 по протоколу CommerceML

Модуль обмена 1С и Drupal 8.<br />
На каждый обмен создаётся нода cml, которая содержит в себе файлы:
 * import.xml - структуру ткаталога и товары
 * offers.xml - предложения по товарам (цена и количество)

Прилетевшие картинки кладутся в папку files<br>
Список всех обменов доступен на странице /cml<br>

Адрес для обмена: http://***.n2.s1dev.ru/cmlservice/1c <br />
Страница настроек: /admin/config/system/cmlservice

<img src="https://github.com/politsin/help/blob/master/1csett.png?raw=true">


